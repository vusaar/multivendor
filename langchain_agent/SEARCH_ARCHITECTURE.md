# Hybrid Search Architecture: Lexical + Semantic

This document explains the technical implementation of the search system in the `langchain_agent`. The system is a **Hybrid Search** engine that combines traditional keyword matching with AI-driven conceptual understanding.

## 1. Overview
Modern search involves two distinct challenges:
- **Lexical Gap**: Users might use different words for the same thing (e.g., "soda" vs "soft drink").
- **Precision**: Users expect exact matches for brand names or model numbers (e.g., "iPhone 15").

To solve both, we implement **Hybrid Search** with **Reciprocal Rank Fusion (RRF)**.

---

## 2. The Lexical Layer (Syntax)
Powered by the `pg_trgm` extension in PostgreSQL.

### How it works:
It breaks words into "trigrams" (sequences of 3 characters). For example, "shirt" becomes `{shi, hir, irt}`. 

### Why we use it:
- **Fuzzy Matching**: It can handle typos (e.g., "shrt" will still match "shirt").
- **Exact Matches**: It gives extremely high scores to exact character overlaps.

### Implementation:
We use the `similarity(text, query)` function. In our ranking, we **weight the product name twice as much as the description** to ensure title matches appear first.

```sql
(similarity(name, $query) * 2 + similarity(description, $query))
```

---

## 3. The Semantic Layer (Concepts)
Powered by `pgvector` and Google GenAI Embeddings.

### The Embedding Process:
1. **Flattening**: We take a product's name, category, description, and attributes and "flatten" them into one string.
2. **Vectorization**: We send that string to Google. It returns a **3072-dimensional vector** (a list of 3072 numbers representing the "address" of that product in a "field of meaning").
3. **Storage**: This vector is stored in the `embedding` column as a specialized `vector(3072)` type.

### How it searches:
When a user searches, we convert their query into a vector and use **Cosine Distance (`<=>`)** to find products whose "meaning" is closest to the query.

```sql
ORDER BY embedding <=> $query_vector
```

---

## 4. The Merger: Reciprocal Rank Fusion (RRF)
This is the "magic" algorithm that combines the two lists.

### The Problem:
You can't just add a Lexical score (0.0 to 1.0) to a Semantic score (0.0 to 2.0). They are different scales.

### The Solution (RRF):
Instead of scores, we use **Ranks**. 
- If a product is #1 in Lexical and #10 in Semantic, we calculate its score based on its position in both lists.
- **Formula**: `1 / (k + rank_lexical) + 1 / (k + rank_semantic)` (Default `k = 60`).

### Benefits:
- **Fairness**: No single strategy can "overpower" the other.
- **Relevance**: Products that appear in the top of *both* lists are boosted significantly.
- **Handling Zeroes**: If a product doesn't appear in one list at all, it simply gets a 0 for that half, but can still win if it's #1 in the other.

---

## 5. Implementation Files
- **[embeddings.service.ts](file:///c:/xampp4/htdocs/multistore/langchain_agent/src/services/embeddings.service.ts)**: Handles the connection to Google AI.
- **[vector_search.tool.ts](file:///c:/xampp4/htdocs/multistore/langchain_agent/src/tools/vector_search.tool.ts)**: Contains the SQL logic for RRF and hybridization.
- **[sync-embeddings.ts](file:///c:/xampp4/htdocs/multistore/langchain_agent/scripts/sync-embeddings.ts)**: The "ETL" script to keep your database vectors up to date.

## 6. Maintenance
Whenever you add a large batch of products, you should run the sync script:
```bash
cd langchain_agent
npx ts-node scripts/sync-embeddings.ts
```
