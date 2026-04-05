from __future__ import annotations

import hashlib
import os
from dataclasses import dataclass
from typing import Iterable

from dotenv import load_dotenv
from sqlalchemy import create_engine, text
from sqlalchemy.engine import Engine

from langchain_openai import OpenAIEmbeddings
from langchain_qdrant import QdrantVectorStore
from qdrant_client import QdrantClient
from langchain.schema import Document

from .settings import load_settings


load_dotenv()


@dataclass(frozen=True)
class PublicDoc:
    doc_id: str
    language: str
    title: str
    content: str
    source: str
    tags: list[str]


def _hash_content(s: str) -> str:
    return hashlib.sha256(s.encode("utf-8")).hexdigest()


def _mysql_engine() -> Engine:
    mysql_url = os.environ.get("MYSQL_URL")
    if not mysql_url:
        raise RuntimeError("MYSQL_URL is required for ingestion (restore your SQL dump into MySQL and point to it).")
    return create_engine(mysql_url, pool_pre_ping=True)


def _extract_public_docs(engine: Engine) -> Iterable[PublicDoc]:
    # Public-only extractor (starter):
    # - chalets: public description fields
    # - amenities: names/categories
    # NOTE: Adjust queries/fields depending on your schema.
    with engine.connect() as conn:
        # Chalets
        chalets = conn.execute(
            text(
                """
                SELECT id, name, slug, location, description, price_per_night, max_guests, bedrooms, bathrooms, is_active
                FROM chalets
                WHERE is_active = 1
                """
            )
        ).mappings()
        for c in chalets:
            content = (
                f"اسم الشاليه: {c.get('name')}\n"
                f"الموقع: {c.get('location')}\n"
                f"الوصف: {c.get('description')}\n"
                f"السعر لكل ليلة: {c.get('price_per_night')}\n"
                f"الحد الأعلى للضيوف: {c.get('max_guests')}\n"
                f"الغرف: {c.get('bedrooms')}، الحمامات: {c.get('bathrooms')}\n"
            )
            yield PublicDoc(
                doc_id=f"chalet:{c.get('id')}",
                language="ar",
                title=str(c.get("name") or ""),
                content=content,
                source="db:chalets",
                tags=["chalets", "public"],
            )

        # Amenities
        amenities = conn.execute(
            text(
                """
                SELECT id, name, category, is_active
                FROM amenities
                WHERE is_active = 1
                """
            )
        ).mappings()
        for a in amenities:
            content = f"مرفق: {a.get('name')}\nالفئة: {a.get('category')}\n"
            yield PublicDoc(
                doc_id=f"amenity:{a.get('id')}",
                language="ar",
                title=str(a.get("name") or ""),
                content=content,
                source="db:amenities",
                tags=["amenities", "public"],
            )


def _chunk_documents(docs: Iterable[PublicDoc]) -> list[Document]:
    # Simple chunking (starter): no token-aware splitting yet.
    # For large content you can replace with RecursiveCharacterTextSplitter.
    out: list[Document] = []
    for d in docs:
        content_hash = _hash_content(d.content)
        out.append(
            Document(
                page_content=d.content.strip(),
                metadata={
                    "doc_id": d.doc_id,
                    "chunk_id": f"{d.doc_id}:0",
                    "source": d.source,
                    "language": d.language,
                    "tags": d.tags,
                    "is_public": True,
                    "content_hash": content_hash,
                },
            )
        )
    return out


def main() -> None:
    settings = load_settings()
    engine = _mysql_engine()

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

    public_docs = list(_extract_public_docs(engine))
    lc_docs = _chunk_documents(public_docs)

    if not lc_docs:
        print("No public docs extracted. Check MYSQL_URL and schema.")
        return

    # Upsert
    vector_store.add_documents(lc_docs)
    print(f"Indexed {len(lc_docs)} documents into Qdrant collection '{settings.qdrant_collection}'.")


if __name__ == "__main__":
    main()

