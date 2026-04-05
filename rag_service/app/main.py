from __future__ import annotations

from fastapi import FastAPI, Header, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from dotenv import load_dotenv

from .schemas import ChatRequest, ChatResponse
from .settings import load_settings
from .rag import build_rag_chain, answer_with_rag


load_dotenv()

settings = load_settings()
vector_store, llm = build_rag_chain(settings)

app = FastAPI(title="ChaletGo RAG Service", version="0.1.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=False,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.get("/health")
def health():
    return {"ok": True}


@app.post("/rag/chat", response_model=ChatResponse)
async def rag_chat(
    req: ChatRequest,
    x_rag_proxy_secret: str | None = Header(default=None, alias="X-Rag-Proxy-Secret"),
):
    if not x_rag_proxy_secret or x_rag_proxy_secret != settings.rag_proxy_secret.get_secret_value():
        raise HTTPException(status_code=401, detail="Unauthorized")

    language = (req.language or "ar").lower()
    if language not in ("ar", "en"):
        language = "ar"

    return await answer_with_rag(
        settings=settings,
        vector_store=vector_store,
        llm=llm,
        question=req.question,
        language=language,
    )

