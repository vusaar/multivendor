"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.categorySearchTool = exports.hybridSearchTool = void 0;
exports.executeHybridSearch = executeHybridSearch;
const tools_1 = require("@langchain/core/tools");
const zod_1 = require("zod");
const database_1 = require("../config/database");
const embeddings_service_1 = require("../services/embeddings.service");
/**
 * Core search logic that executes the SQL query.
 * Can be called by the tool or directly by the agent for stateful bypass.
 */
async function executeHybridSearch(params) {
    const { query, embedding, limit, offset = 0, min_price, max_price, entity, synonyms, categories, attributes } = params;
    console.log(`[DB] Structured Search Params:`, { query, limit, offset, entity, synonyms, categories, attributes });
    // We will build a single CTE query
    let priceFilter = "";
    // PGVector requires the array to be formatted as a string starting with brackets
    const embeddingStr = `[${embedding.join(',')}]`;
    const sqlParams = [embeddingStr, limit, offset]; // $1 = embedding, $2 = limit, $3 = offset
    if (min_price !== undefined) {
        sqlParams.push(min_price);
        priceFilter += ` AND price >= $${sqlParams.length}`;
    }
    if (max_price !== undefined) {
        sqlParams.push(max_price);
        priceFilter += ` AND price <= $${sqlParams.length}`;
    }
    // Phase 2: Precision Scoring Setup
    let precisionScoreSql = `0.0`;
    // Helper to escape strings for ILIKE safely (simple replace for single quotes, 
    // though parameterization is better, dynamic ILIKE with dynamic params is tricky in pg pass-through)
    // We will use parameterization for all dynamic values to be safe.
    // Entity Match (+50 points)
    if (entity) {
        sqlParams.push(entity.toLowerCase().replace(/[^a-z0-9]/g, ''));
        const pIdx = sqlParams.length;
        precisionScoreSql += ` + (CASE WHEN regexp_replace(name, '[^a-zA-Z0-9]', '', 'g') ILIKE '%' || $${pIdx} || '%' THEN 50.0 ELSE 0.0 END)`;
    }
    // Synonym Matches (+40 points) - LLM Query Expansion
    if (synonyms && synonyms.length > 0) {
        synonyms.forEach(syn => {
            sqlParams.push(syn.toLowerCase().replace(/[^a-z0-9]/g, ''));
            const pIdx = sqlParams.length;
            precisionScoreSql += ` + (CASE WHEN regexp_replace(name, '[^a-zA-Z0-9]', '', 'g') ILIKE '%' || $${pIdx} || '%' THEN 40.0 ELSE 0.0 END)`;
        });
    }
    // Category Match (+30 points per category)
    if (categories && categories.length > 0) {
        categories.forEach(cat => {
            sqlParams.push(cat.toLowerCase());
            const pIdx = sqlParams.length;
            precisionScoreSql += ` + (CASE WHEN LOWER(search_context) ILIKE '%categorypath: %' || $${pIdx} || '%' THEN 30.0 ELSE 0.0 END)`;
        });
    }
    // Attribute Match (+10 points per attribute)
    if (attributes && attributes.length > 0) {
        attributes.forEach(attr => {
            sqlParams.push(attr.toLowerCase());
            const pIdx = sqlParams.length;
            precisionScoreSql += ` + (CASE WHEN LOWER(search_context) ILIKE '%' || $${pIdx} || '%' THEN 10.0 ELSE 0.0 END)`;
        });
    }
    // Phase 1: High-Recall Pre-Filtering (Get top 200 conceptually similar items)
    // Phase 3: Final Rank = (Vector * 10) + Precision Score
    const sql = `
        WITH semantic_candidates AS (
            SELECT id, name, price, description, search_context,
                   (1 - (embedding <=> $1::vector)) AS vector_score
            FROM products
            WHERE status = 'active' AND embedding IS NOT NULL
              AND (1 - (embedding <=> $1::vector)) > 0.74 -- Balanced net
            ${priceFilter}
            ORDER BY vector_score DESC
            LIMIT 200
        )
        SELECT 
            id, name, price, description,
            (
                (vector_score * 10) + -- Scale vector to ~6-10 points to act as a tie-breaker/base
                ${precisionScoreSql}
            ) AS rrf_score 
        FROM semantic_candidates
        ORDER BY rrf_score DESC
        LIMIT $2 OFFSET $3;
    `;
    const res = await database_1.db.query(sql, sqlParams);
    return res.rows;
}
/**
 * Hybrid search combining Trigram (Keyword) and Vector (Semantic) search.
 * Uses Reciprocal Rank Fusion (RRF) for ranking.
 */
exports.hybridSearchTool = new tools_1.DynamicStructuredTool({
    name: "hybrid_product_search",
    description: "Search for products using both keyword matches and semantic concepts. Handles hierarchy and attributes.",
    schema: zod_1.z.object({
        query: zod_1.z.string().describe("The user's search query (e.g., 'warm winter jacket')"),
        entity: zod_1.z.string().optional().describe("The primary product type (e.g., 'tshirt', 'jeans', 'laptop')"),
        synonyms: zod_1.z.array(zod_1.z.string()).optional().describe("1 to 3 direct synonyms for the primary product type, especially if it's a colloquial term (e.g., ['sweater', 'pullover'] for 'jumper', or ['shirt', 'blouse'] for 'top')"),
        categories: zod_1.z.array(zod_1.z.string()).optional().describe("Target demographics, departments, or high-level categories (e.g., ['men', 'beauty', 'electronics'])"),
        attributes: zod_1.z.array(zod_1.z.string()).optional().describe("Specific attributes like color, size, brand, or material (e.g., ['blue', 'XL', 'nike', 'cotton'])"),
        limit: zod_1.z.number().optional().default(5).describe("Number of results to return"),
        min_price: zod_1.z.number().optional().describe("Minimum price"),
        max_price: zod_1.z.number().optional().describe("Maximum price"),
    }),
    func: async ({ query, entity, synonyms, categories, attributes, limit, min_price, max_price }) => {
        let finalMin = min_price;
        let finalMax = max_price;
        // Auto-extract price from query if not provided
        if (finalMax === undefined) {
            const underMatch = query.match(/(?:under|less than|below|max)\s*[$€£]?\s*(\d+(?:\.\d{2})?)/i);
            if (underMatch)
                finalMax = parseFloat(underMatch[1]);
        }
        if (finalMin === undefined) {
            const aboveMatch = query.match(/(?:above|more than|greater than|min|over)\s*[$€£]?\s*(\d+(?:\.\d{2})?)/i);
            if (aboveMatch)
                finalMin = parseFloat(aboveMatch[1]);
        }
        console.log(`[AGENT] Tool hybrid_product_search called. Query: ${query}, Entity: ${entity}, Cats: ${categories}, Attrs: ${attributes}`);
        try {
            const queryEmbedding = await embeddings_service_1.embeddingsService.generateEmbedding(query);
            const rows = await executeHybridSearch({
                query,
                embedding: queryEmbedding,
                limit,
                min_price: finalMin,
                max_price: finalMax,
                entity,
                synonyms,
                categories,
                attributes
            });
            if (rows.length === 0) {
                return JSON.stringify({ status: "no_results", message: `No products found for "${query}"` });
            }
            return JSON.stringify(rows);
        }
        catch (error) {
            console.error("Hybrid Search Error:", error);
            return JSON.stringify({ status: "error", message: error.message });
        }
    },
});
/**
 * Semantic search for categories to help the agent find the right category ID.
 */
exports.categorySearchTool = new tools_1.DynamicStructuredTool({
    name: "search_categories_semantically",
    description: "Find product categories based on semantic meaning. Useful when the user's category name doesn't exactly match the database.",
    schema: zod_1.z.object({
        query: zod_1.z.string().describe("The category to look for (e.g., 'clothing for cold weather')"),
    }),
    func: async ({ query }) => {
        console.log(`Tool search_categories_semantically called with query: ${query} `);
        try {
            const queryEmbedding = await embeddings_service_1.embeddingsService.generateEmbedding(query);
            const embeddingStr = `[${queryEmbedding.join(",")}]`;
            const sql = `
                SELECT id, name, description,
                (1 - (embedding <=> $1::vector)) as similarity
                FROM categories
                WHERE embedding IS NOT NULL
                ORDER BY embedding <=> $1:: vector
                LIMIT 5;
`;
            const res = await database_1.db.query(sql, [embeddingStr]);
            return JSON.stringify(res.rows);
        }
        catch (error) {
            console.error("Category Search Error:", error);
            return JSON.stringify({ status: "error", message: error.message });
        }
    },
});
