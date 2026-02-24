import { DynamicStructuredTool } from "@langchain/core/tools";
import { z } from "zod";
import { db } from "../config/database";
import { embeddingsService } from "../services/embeddings.service";

/**
 * Hybrid search combining Trigram (Keyword) and Vector (Semantic) search.
 * Uses Reciprocal Rank Fusion (RRF) for ranking.
 */
export const hybridSearchTool = new DynamicStructuredTool({
    name: "hybrid_product_search",
    description: "Search for products using both keyword matches and semantic concepts. Best for vague or multi-attribute queries.",
    schema: z.object({
        query: z.string().describe("The user's search query (e.g., 'warm winter jacket' or 'blue electronics')"),
        limit: z.number().optional().default(5).describe("Number of results to return"),
        min_price: z.number().optional().describe("Strict minimum price (e.g., if user says 'above 10', pass 10)"),
        max_price: z.number().optional().describe("Strict maximum price (e.g., if user says 'less than 50', pass 50)"),
    }),
    func: async ({ query, limit, min_price, max_price }) => {
        let finalMin = min_price;
        let finalMax = max_price;

        // Auto-extract price from query if not provided as parameters
        if (finalMax === undefined) {
            const underMatch = query.match(/(?:under|less than|below|max)\s*[$€£]?\s*(\d+(?:\.\d{2})?)/i);
            if (underMatch) finalMax = parseFloat(underMatch[1]);
        }
        if (finalMin === undefined) {
            const aboveMatch = query.match(/(?:above|more than|greater than|min|over)\s*[$€£]?\s*(\d+(?:\.\d{2})?)/i);
            if (aboveMatch) finalMin = parseFloat(aboveMatch[1]);
        }

        console.log(`[AGENT] Tool hybrid_product_search called with query: ${query}, min: ${finalMin}, max: ${finalMax}`);
        const totalStart = Date.now();
        try {
            const embStart = Date.now();
            const queryEmbedding = await embeddingsService.generateEmbedding(query);
            console.log(`[PERF] Embedding generation took: ${Date.now() - embStart}ms`);

            const embeddingStr = `[${queryEmbedding.join(",")}]`;

            // RRF Algorithm implementation in SQL
            const k = 60;

            let priceFilter = "";
            const params: any[] = [query, embeddingStr, k, limit];

            if (finalMin !== undefined) {
                params.push(finalMin);
                priceFilter += ` AND price >= $${params.length}`;
            }
            if (finalMax !== undefined) {
                params.push(finalMax);
                priceFilter += ` AND price <= $${params.length}`;
            }

            const sql = `
                WITH keyword_results AS (
                    SELECT id, name, 
                           ROW_NUMBER() OVER (ORDER BY similarity(search_context, $1) DESC) as rank
                    FROM products
                    WHERE status = 'active' AND similarity(search_context, $1) > 0.05
                    ${priceFilter}
                    LIMIT 50
                ),
                semantic_results AS (
                    SELECT id, name,
                           ROW_NUMBER() OVER (ORDER BY embedding <=> $2::vector) as rank
                    FROM products
                    WHERE status = 'active' AND embedding IS NOT NULL
                    ${priceFilter}
                    LIMIT 50
                )
                SELECT 
                    p.id, p.name, p.price, p.description,
                    COALESCE(1.0 / ($3 + kr.rank), 0.0) + COALESCE(1.0 / ($3 + sr.rank), 0.0) as rrf_score
                FROM products p
                LEFT JOIN keyword_results kr ON p.id = kr.id
                LEFT JOIN semantic_results sr ON p.id = sr.id
                WHERE kr.id IS NOT NULL OR sr.id IS NOT NULL
                ORDER BY rrf_score DESC
                LIMIT $4;
            `;

            const dbStart = Date.now();
            const res = await db.query(sql, params);
            console.log(`[PERF] DB query took: ${Date.now() - dbStart}ms`);

            console.log(`[PERF] Total hybrid_product_search tool took: ${Date.now() - totalStart}ms`);

            if (res.rows.length === 0) {
                return JSON.stringify({ status: "no_results", message: `No products found for "${query}"` });
            }

            return JSON.stringify(res.rows);
        } catch (error: any) {
            console.error("Hybrid Search Error:", error);
            return JSON.stringify({ status: "error", message: error.message });
        }
    },
});

/**
 * Semantic search for categories to help the agent find the right category ID.
 */
export const categorySearchTool = new DynamicStructuredTool({
    name: "search_categories_semantically",
    description: "Find product categories based on semantic meaning. Useful when the user's category name doesn't exactly match the database.",
    schema: z.object({
        query: z.string().describe("The category to look for (e.g., 'clothing for cold weather')"),
    }),
    func: async ({ query }) => {
        console.log(`Tool search_categories_semantically called with query: ${query} `);
        try {
            const queryEmbedding = await embeddingsService.generateEmbedding(query);
            const embeddingStr = `[${queryEmbedding.join(",")}]`;

            const sql = `
                SELECT id, name, description,
                (1 - (embedding <=> $1::vector)) as similarity
                FROM categories
                WHERE embedding IS NOT NULL
                ORDER BY embedding <=> $1:: vector
                LIMIT 5;
`;

            const res = await db.query(sql, [embeddingStr]);
            return JSON.stringify(res.rows);
        } catch (error: any) {
            console.error("Category Search Error:", error);
            return JSON.stringify({ status: "error", message: error.message });
        }
    },
});
