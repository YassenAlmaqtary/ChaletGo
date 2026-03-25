# ChaletGo RAG Service (FastAPI + LangChain + Qdrant)

This service powers the mobile-app chatbot using Retrieval-Augmented Generation (RAG).

## What it does
- Receives a question from Laravel (authenticated proxy).
- Retrieves relevant chunks from Qdrant.
- Calls an LLM (OpenAI) **using only retrieved context**.
- Returns JSON with: `answer`, `citations`, `confidence`, `needs_human_handoff`, `fallback_message`.

## Environment variables
Create a `.env` (or export variables):

- `RAG_PROXY_SECRET`: shared secret, must match Laravel `RAG_PROXY_SECRET`.
- `OPENAI_API_KEY`: OpenAI API key.
- `OPENAI_MODEL`: default `gpt-4o-mini` (or any supported chat model).
- `OPENAI_EMBEDDING_MODEL`: default `text-embedding-3-small`.
- `QDRANT_URL`: e.g. `http://qdrant:6333` or `http://127.0.0.1:6333`.
- `QDRANT_API_KEY`: optional.
- `QDRANT_COLLECTION`: default `chaletgo_public`.

## Run locally
```bash
python -m venv .venv
./.venv/Scripts/pip install -r requirements.txt  # on Windows
./.venv/Scripts/uvicorn app.main:app --host 0.0.0.0 --port 9000
```

## Ingestion (publicOnly)
This repo includes a starter ingestion script that reads from a MySQL database and indexes
public knowledge into Qdrant.

You can restore your SQL dump into a MySQL instance, then point the ingester to it.

```bash
set MYSQL_URL=mysql+pymysql://user:pass@127.0.0.1:3306/ChaletGoDb
./.venv/Scripts/python -m app.ingest
```

