from __future__ import annotations

from pydantic import BaseModel
from pydantic import Field
from pydantic import SecretStr
from pydantic import ValidationError
import os


class Settings(BaseModel):
    rag_proxy_secret: SecretStr = Field(alias="RAG_PROXY_SECRET")

    openai_api_key: SecretStr = Field(alias="OPENAI_API_KEY")
    openai_model: str = Field(default="gpt-4o-mini", alias="OPENAI_MODEL")
    openai_embedding_model: str = Field(default="text-embedding-3-small", alias="OPENAI_EMBEDDING_MODEL")

    qdrant_url: str = Field(default="http://127.0.0.1:6333", alias="QDRANT_URL")
    qdrant_api_key: SecretStr | None = Field(default=None, alias="QDRANT_API_KEY")
    qdrant_collection: str = Field(default="chaletgo_public", alias="QDRANT_COLLECTION")

    retrieval_top_k: int = Field(default=8, alias="RETRIEVAL_TOP_K")
    min_citations: int = Field(default=2, alias="MIN_CITATIONS")
    min_score: float = Field(default=0.25, alias="MIN_SCORE")


def load_settings() -> Settings:
    # Do not use BaseSettings to keep deps minimal and explicit.
    try:
        return Settings.model_validate(os.environ)
    except ValidationError as e:
        raise RuntimeError(f"Invalid RAG settings: {e}") from e

