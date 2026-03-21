# Hybrid Search & Ranking Architecture

This document details the technical implementation of the search and ranking engine. The system combines **Lexical (Keyword)** and **Semantic (Meaning)** search, merged through **Reciprocal Rank Fusion (RRF)** and refined by a **Tiered Boosting** system.

---

## 1. The Retrieval Layers

### A. Lexical Layer (Keyword)
- **Engine**: PostgreSQL `pg_trgm` (Trigram similarity).
- **Scope**: Matches exact or partial character sequences in `name` and `search_context`.
- **Threshold**: `similarity > 0.2`. This excludes items with no character overlap.

### B. Semantic Layer (Meaning)
- **Engine**: PostgreSQL `pgvector` with Google GenAI Embeddings (`text-embedding-004`).
- **Scope**: 3072-dimensional vector search on "flattened" product data.
- **Threshold**: `similarity > 0.85`. This "similarity floor" ensures that conceptually unrelated items (e.g., "Nail Polish" vs "Hat") are rejected.

---

## 2. The Tiered Scoring & Boosting

To ensure that user intent is prioritized (e.g., if you ask for a "t-shirt", you should see t-shirts first), we apply a multi-tiered weighting system *before* the ranking:

### Tier 0: Global Category Boost (+1.0 Score)
If the AI identifies a target **Department** or **Demographic** (e.g., "men", "beauty"):
- Any product in that category receives a massive **+1.0 override** to its final score.
- This ensures that a correctly categorized result (Score ~1.04) always beats an uncategorized result (Score ~0.04).

### Tier 1: Entity Match (Weight: 50.0)
If the user asks for a specific **Product Type** (e.g., "shirt"):
- Products whose name contains the extracted entity receive a Rank #1 boost in the lexical list.

### Tier 2: Attribute Match (Weight: 12.0)
If the user asks for a specific **Attribute** (e.g., "blue"):
- Products matching the attribute gain relevance *within* their entity group.

---

## 3. The Ranking Algorithm: RRF

We use **Reciprocal Rank Fusion (RRF)** to merge the Keyword and Semantic lists.

**Formula**:
`rrf_score = (1 / (40 + rank_keyword)) + (1 / (40 + rank_semantic)) + Boost`

- **`rank_keyword`**: Position (1-100) in the lexical result set.
- **`rank_semantic`**: Position (1-100) in the vector result set.
- **`40` (k)**: The smoothing constant that prevents low-rank items from overpowering the top results.

---

## 4. Interaction Thresholds (Confidence)

To prevent showing "noisy" results to the user, the agent applying two "gates":

| Score Range | Tier | Action |
| :--- | :--- | :--- |
| **> 1.0** | **Verified Match** | Shown immediately (Right Category + Right Item). |
| **0.03 - 1.0** | **Partial Match** | Shown immediately (Right Item, but maybe wrong category). |
| **0.015 - 0.025**| **Potential Suggestion**| Hidden behind a **"Show Suggestions"** button. |
| **< 0.015** | **Noise** | Rejected entirely. |

---

## 5. System Components

1.  **[vector_search.tool.ts](file:///c:/xampp4/htdocs/multistore/langchain_agent/src/tools/vector_search.tool.ts)**: The "Brain" containing the SQL RRF logic and weights.
2.  **[search.agent.ts](file:///c:/xampp4/htdocs/multistore/langchain_agent/src/services/search.agent.ts)**: The Intent Extractor that identifies Entities, Categories, and Attributes.
3.  **[message.processor.service.ts](file:///c:/xampp4/htdocs/multistore/langchain_agent/src/services/message.processor.service.ts)**: The Presenter that applies the 0.025 threshold and manages the WhatsApp UI flow.

---

## 6. Maintenance Commands

Sync embeddings after a bulk product import:
```bash
# Artisan command (Laravel)
php artisan products:sync-embeddings --force

# Direct script (Agent)
cd langchain_agent && npm run build && npx ts-node scripts/sync-embeddings.ts
```
