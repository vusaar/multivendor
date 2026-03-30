import { DynamicStructuredTool } from "@langchain/core/tools";
import { z } from "zod";
import { db } from "../config/database";
import { embeddingsService } from "../services/embeddings.service";

/**
 * Core search logic that executes the SQL query. 
 */
export async function executeHybridSearch(params: {
    query: string;
    embedding: number[];
    limit: number;
    offset?: number;
    min_price?: number;
    max_price?: number;
    entity?: string;
    synonyms?: string[];
    categories?: string[];
    attributes?: string[];
}) {
    const { query, embedding, limit, offset = 0, min_price, max_price, entity, synonyms, categories, attributes } = params;
    
    let priceFilter = "";
    const embeddingStr = `[${embedding.join(',')}]`;
    const sqlParams: any[] = [embeddingStr, limit, offset]; // $1, $2, $3

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
    const THRESHOLD = '0.65';
    
    // 0. Literal Query Priority (+200)
    sqlParams.push(query.toLowerCase().trim());
    const qIdx = sqlParams.length;
    precisionScoreSql += ` + (CASE WHEN LOWER(name) = $${qIdx} THEN 200.0 ELSE 0.0 END)`;
    precisionScoreSql += ` + (CASE WHEN REPLACE(LOWER(name), '-', '') = REPLACE($${qIdx}, '-', '') THEN 200.0 ELSE 0.0 END)`;
    
    // Continuous fuzzy match (Explicit threshold for reliability)
    precisionScoreSql += ` + (CASE WHEN word_similarity($${qIdx}, LOWER(name)) >= ${THRESHOLD} THEN word_similarity($${qIdx}, LOWER(name)) * 80.0 ELSE 0.0 END)`;
    precisionScoreSql += ` + (CASE WHEN word_similarity($${qIdx}, LOWER(description)) >= ${THRESHOLD} THEN word_similarity($${qIdx}, LOWER(description)) * 40.0 ELSE 0.0 END)`;

    // 1. Entity Match
    if (entity) {
        sqlParams.push(entity.toLowerCase().trim());
        const pIdx = sqlParams.length;
        // Strict similarity for entities to prevent lexical overlap (e.g., shoe vs shirt)
        precisionScoreSql += ` + (CASE WHEN strict_word_similarity($${pIdx}, LOWER(name)) >= ${THRESHOLD} THEN strict_word_similarity($${pIdx}, LOWER(name)) * 200.0 ELSE 0.0 END)`;
        precisionScoreSql += ` + (CASE WHEN word_similarity($${pIdx}, LOWER(search_context)) >= ${THRESHOLD} THEN word_similarity($${pIdx}, LOWER(search_context)) * 60.0 ELSE 0.0 END)`;
        precisionScoreSql += ` + (CASE WHEN word_similarity($${pIdx}, LOWER(description)) >= ${THRESHOLD} THEN word_similarity($${pIdx}, LOWER(description)) * 30.0 ELSE 0.0 END)`;
        // Exact small priority bonus
        precisionScoreSql += ` + (CASE WHEN LOWER(name) = $${pIdx} THEN 50.0 ELSE 0.0 END)`;
    }

    // Synonym Matches (High weight to reach VERIFIED threshold)
    if (synonyms && synonyms.length > 0) {
        synonyms.forEach(syn => {
            sqlParams.push(syn.toLowerCase().trim());
            const pIdx = sqlParams.length;
            precisionScoreSql += ` + (CASE WHEN word_similarity($${pIdx}, LOWER(name)) >= 0.5 THEN word_similarity($${pIdx}, LOWER(name)) * 200.0 ELSE 0.0 END)`;
            precisionScoreSql += ` + (CASE WHEN word_similarity($${pIdx}, LOWER(search_context)) >= 0.5 THEN word_similarity($${pIdx}, LOWER(search_context)) * 40.0 ELSE 0.0 END)`;
        });
    }

    // Category Match
    if (categories && categories.length > 0) {
        categories.forEach(cat => {
            sqlParams.push(cat.toLowerCase());
            const pIdx = sqlParams.length;
            precisionScoreSql += ` + (CASE WHEN LOWER(search_context) ILIKE '%categorypath: %' || $${pIdx} || '%' THEN 100.0 ELSE 0.0 END)`;
        });
    }

    // Attribute Match
    if (attributes && attributes.length > 0) {
        attributes.forEach(attr => {
            sqlParams.push(attr.toLowerCase().trim());
            const pIdx = sqlParams.length;
            precisionScoreSql += ` + (CASE WHEN word_similarity(LOWER(search_context), $${pIdx}) >= 0.5 THEN word_similarity(LOWER(search_context), $${pIdx}) * 30.0 ELSE 0.0 END)`;
            precisionScoreSql += ` + (CASE WHEN LOWER(name) ILIKE '%' || $${pIdx} || '%' THEN 150.0 ELSE 0.0 END)`;
            precisionScoreSql += ` + (CASE WHEN LOWER(search_context) ILIKE '%' || $${pIdx} || '%' THEN 80.0 ELSE 0.0 END)`;
        });
    }

    const finalSql = `
        WITH semantic_candidates AS (
            SELECT id, name, price, description, search_context, vendor_id, category_id, status,
                   (1 - (embedding <=> $1::vector)) AS vector_score
            FROM products
            WHERE status = 'active' AND embedding IS NOT NULL
              AND (1 - (embedding <=> $1::vector)) > 0.74 
            ${priceFilter}
            ORDER BY vector_score DESC
            LIMIT 200
        )
        SELECT 
            id, name, price, description, vendor_id, category_id, status, search_context,
            (
                (vector_score * 10) + 
                ${precisionScoreSql}
            ) AS rrf_score 
        FROM semantic_candidates
        ORDER BY rrf_score DESC
        LIMIT $2 OFFSET $3;
    `;

    console.log(`[DB] EXECUTING SQL:`, finalSql);
    console.log(`[DB] PARAMS:`, sqlParams);

    const res = await db.query(finalSql, sqlParams);
    return res.rows;
}

export const hybridSearchTool = new DynamicStructuredTool({
    name: "hybrid_product_search",
    description: "Search for products using both keyword matches and semantic concepts. Handles hierarchy and attributes.",
    schema: z.object({
        query: z.string().describe("The user's search query (e.g., 'warm winter jacket')"),
        entity: z.string().optional().describe("The primary product type (e.g., 'tshirt', 'jeans', 'laptop')"),
        synonyms: z.array(z.string()).optional().describe("1 to 3 direct synonyms for the primary product type, especially if it's a colloquial term (e.g., ['sweater', 'pullover'] for 'jumper', or ['shirt', 'blouse'] for 'top')"),
        categories: z.array(z.string()).optional().describe("Target demographics, departments, or high-level categories (e.g., ['men', 'beauty', 'electronics'])"),
        attributes: z.array(z.string()).optional().describe("Specific attributes like color, size, brand, or material (e.g., ['blue', 'XL', 'nike', 'cotton'])"),
        limit: z.number().optional().default(5).describe("Number of results to return"),
        min_price: z.number().optional().describe("Minimum price"),
        max_price: z.number().optional().describe("Maximum price"),
    }),
    func: async ({ query, entity, synonyms, categories, attributes, limit, min_price, max_price }) => {
        let finalMin = min_price;
        let finalMax = max_price;

        if (finalMax === undefined) {
            const underMatch = query.match(/(?:under|less than|below|max)\s*[$€£]?\s*(\d+(?:\.\d{2})?)/i);
            if (underMatch) finalMax = parseFloat(underMatch[1]);
        }
        if (finalMin === undefined) {
            const aboveMatch = query.match(/(?:above|more than|greater than|min|over)\s*[$€£]?\s*(\d+(?:\.\d{2})?)/i);
            if (aboveMatch) finalMin = parseFloat(aboveMatch[1]);
        }

        try {
            const queryEmbedding = await embeddingsService.generateEmbedding(query);
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
        } catch (error: any) {
            console.error("Hybrid Search Error:", error);
            return JSON.stringify({ status: "error", message: error.message });
        }
    },
});

export const categorySearchTool = new DynamicStructuredTool({
    name: "search_categories_semantically",
    description: "Find product categories based on semantic meaning. Useful when the user's category name doesn't exactly match the database.",
    schema: z.object({
        query: z.string().describe("The category to look for (e.g., 'clothing for cold weather')"),
    }),
    func: async ({ query }) => {
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
