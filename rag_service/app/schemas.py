from __future__ import annotations

from pydantic import BaseModel, Field


class UserContext(BaseModel):
    user_type: str | None = None
    user_id: int | None = None


class ChatRequest(BaseModel):
    question: str = Field(min_length=2, max_length=2000)
    language: str = Field(default="ar")
    conversation_id: str | None = Field(default=None, max_length=128)
    user_context: UserContext | None = None
    public_only: bool = True


class Citation(BaseModel):
    doc_id: str
    chunk_id: str
    source: str
    snippet: str


class ChatResponse(BaseModel):
    answer: str
    citations: list[Citation] = Field(default_factory=list)
    confidence: float = 0.0
    needs_human_handoff: bool = False
    fallback_message: str | None = None

