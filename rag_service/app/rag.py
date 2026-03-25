from __future__ import annotations

import json
from typing import Any

from langchain_openai import ChatOpenAI, OpenAIEmbeddings
from langchain_qdrant import QdrantVectorStore
from qdrant_client import QdrantClient

from .schemas import ChatResponse, Citation
from .settings import Settings


SYSTEM_PROMPT = """You are ChaletGo support assistant.
You MUST answer using ONLY the provided context.

Rules:
- If the context does not contain the answer, respond with a short fallback and set:
  - confidence=0
  - needs_human_handoff=true
  - citations=[]
- Do NOT guess, do NOT invent policies, dates, prices, or conditions.
- Always include citations that support the answer.
- Output MUST be valid JSON matching the schema:
  {{"answer": string, "citations": [{{"doc_id": string, "chunk_id": string, "source": string, "snippet": string}}], "confidence": number, "needs_human_handoff": boolean, "fallback_message": string|null}}
"""


def _build_context(docs: list[Any]) -> tuple[str, list[Citation]]:
    parts: list[str] = []
    citations: list[Citation] = []

    for i, d in enumerate(docs):
        md = d.metadata or {}
        doc_id = str(md.get("doc_id", md.get("source_id", "unknown")))
        chunk_id = str(md.get("chunk_id", md.get("id", f"chunk_{i}")))
        source = str(md.get("source", md.get("content_source", "unknown")))
        snippet = (d.page_content or "").strip()
        snippet_short = snippet[:280]

        citations.append(
            Citation(
                doc_id=doc_id,
                chunk_id=chunk_id,
                source=source,
                snippet=snippet_short,
            )
        )

        parts.append(
            f"[{i+1}] doc_id={doc_id} chunk_id={chunk_id} source={source}\n{snippet}\n"
        )

    return "\n".join(parts).strip(), citations


def build_rag_chain(settings: Settings):
    embeddings = OpenAIEmbeddings(
        model=settings.openai_embedding_model,
        api_key=settings.openai_api_key.get_secret_value(),
    )

    qdrant = QdrantClient(
        url=settings.qdrant_url,
        api_key=settings.qdrant_api_key.get_secret_value() if settings.qdrant_api_key else None,
    )

    vector_store = QdrantVectorStore(
        client=qdrant,
        collection_name=settings.qdrant_collection,
        embedding=embeddings,
    )

    llm = ChatOpenAI(
        model=settings.openai_model,
        api_key=settings.openai_api_key.get_secret_value(),
        temperature=0.2,
    )

    return vector_store, llm


async def answer_with_rag(
    *,
    settings: Settings,
    vector_store: QdrantVectorStore,
    llm: ChatOpenAI,
    question: str,
    language: str,
) -> ChatResponse:
    # Retrieve
    # NOTE: langchain-qdrant retriever doesn't expose score by default here,
    # so we apply min-citations as the main gate.
    retriever = vector_store.as_retriever(
        search_kwargs={
            "k": settings.retrieval_top_k,
            "filter": {
                "must": [
                    {"key": "language", "match": {"value": language}},
                    {"key": "is_public", "match": {"value": True}},
                ]
            },
        }
    )

    docs = await retriever.ainvoke(question)
    context, citations = _build_context(docs)

    if len(citations) < settings.min_citations or context.strip() == "":
        return ChatResponse(
            answer="لا أستطيع الإجابة اعتماداً على المعلومات المتاحة حالياً.",
            citations=[],
            confidence=0.0,
            needs_human_handoff=True,
            fallback_message="لم أجد مصادر كافية للإجابة. يمكنك التواصل مع الدعم.",
        )

    user_prompt = f"""Context:
{context}

Question: {question}
"""

    # Ask LLM for strict JSON
    res = await llm.ainvoke(
        [
            {"role": "system", "content": SYSTEM_PROMPT},
            {"role": "user", "content": user_prompt},
        ]
    )

    text = (res.content or "").strip()
    # Try best-effort JSON extraction
    try:
        data = json.loads(text)
    except Exception:
        # Fallback to safe response
        return ChatResponse(
            answer="تعذر توليد إجابة موثوقة الآن.",
            citations=[],
            confidence=0.0,
            needs_human_handoff=True,
            fallback_message="حاول مرة أخرى أو تواصل مع الدعم.",
        )

    # Force citations to be from retrieved context (no hallucinated sources).
    # We keep only citations whose (doc_id, chunk_id) match retrieved set.
    allowed = {(c.doc_id, c.chunk_id) for c in citations}
    raw_citations = data.get("citations") or []
    filtered: list[Citation] = []
    for c in raw_citations:
        try:
            cc = Citation.model_validate(c)
        except Exception:
            continue
        if (cc.doc_id, cc.chunk_id) in allowed:
            filtered.append(cc)

    # If model didn't cite properly, return fallback (anti-hallucination).
    if len(filtered) < settings.min_citations:
        return ChatResponse(
            answer="لا أستطيع الإجابة اعتماداً على المعلومات المتاحة حالياً.",
            citations=[],
            confidence=0.0,
            needs_human_handoff=True,
            fallback_message="لم أجد مصادر كافية للإجابة. يمكنك التواصل مع الدعم.",
        )

    try:
        out = ChatResponse.model_validate(
            {
                "answer": str(data.get("answer", "")).strip() or "لا أستطيع الإجابة اعتماداً على المعلومات المتاحة حالياً.",
                "citations": [c.model_dump() for c in filtered],
                "confidence": float(data.get("confidence", 0.5)),
                "needs_human_handoff": bool(data.get("needs_human_handoff", False)),
                "fallback_message": data.get("fallback_message"),
            }
        )
        return out
    except Exception:
        return ChatResponse(
            answer="تعذر توليد إجابة موثوقة الآن.",
            citations=[],
            confidence=0.0,
            needs_human_handoff=True,
            fallback_message="حاول مرة أخرى أو تواصل مع الدعم.",
        )

