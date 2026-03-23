import { DynamicStructuredTool } from "@langchain/core/tools";
import { z } from "zod";
import { db } from "../config/database";
import { embeddingsService } from "../services/embeddings.service";

/**
 * Core search logic that executes the SQL query. 
 * Can be called by the tool or directly by the agent for stateful bypass.
 */
export async function executeHybridSearch(params: {
    query: string;
    embedding: number[];
    limit: number;
    offset?: number;
    min_price?: number;
    max_price?: number;
    entity?: string;
    categories?: string[];
    attributes?: string[];
}) {
    const { query, embedding, limit, offset = 0, min_price, max_price, entity, categories, attributes } = params;
    console.log(`[DB] executeHybridSearch Params:`, JSON.stringify({ query, limit, offset, entity, categories, attributes }));
    
    const k = 40;

    let priceFilter = "";
    const sqlParams: any[] = [query, embedding, k, limit, offset];

    if (min_price !== undefined) {
        sqlParams.push(min_price);
        priceFilter += ` AND price >= $${sqlParams.length}`;
    }
    if (max_price !== undefined) {
        sqlParams.push(max_price);
        priceFilter += ` AND price <= $${sqlParams.length}`;
    }

    // Weights configuration (Score Tiers)
    const weightRoot = 60.0;   // Tier 0: Demographic Department (Men/Women)
    const weightSub = 50.0;    // Tier 1: Subcategory Match
    const weightEntity = 50.0; // Tier 1: Primary Entity Match
    const weightAttr = 20.0;   // Tier 2: Attribute Sorting (Color, Brand)

    const cleanSql = (col: string) => `regexp_replace(LOWER(${col}), '[^a-z0-9]', '', 'g')`;

    let weightedScoreSql = `(similarity(products.name, $1) * 3 + similarity(products.search_context, $1))`;
    
    // Use an array to collect all OR conditions for the search
    let searchConditions = [
        `(similarity(name, $1) > 0.2)`,
        `(name ILIKE '%' || $1 || '%')`,
        `(search_context ILIKE '%' || $1 || '%')`
    ];

    if (entity) {
        sqlParams.push(entity.toLowerCase().replace(/[^a-z0-9]/g, ''));
        const pIdx = sqlParams.length;
        weightedScoreSql += ` + (CASE WHEN ${cleanSql('products.name')} ILIKE '%' || $${pIdx} || '%' THEN ${weightEntity} ELSE 0 END)`;
        searchConditions.push(`(${cleanSql('name')} ILIKE '%' || $${pIdx} || '%')`);
        searchConditions.push(`(${cleanSql('search_context')} ILIKE '%' || $${pIdx} || '%')`);
    }

    let categoriesTextArrayIdx = -1;
    if (categories && categories.length > 0) {
        const cleanedCats = categories.map(c => c.toLowerCase());
        sqlParams.push(cleanedCats);
        categoriesTextArrayIdx = sqlParams.length;

        cleanedCats.forEach(cat => {
            sqlParams.push(cat);
            const pIdx = sqlParams.length;
            weightedScoreSql += ` + (CASE 
                WHEN LOWER(products.search_context) ILIKE '%categorypath: ' || $${pIdx} || ' %' THEN ${weightRoot}
                WHEN LOWER(products.search_context) ILIKE '% > ' || $${pIdx} || ' %' THEN ${weightSub}
                WHEN LOWER(products.search_context) ILIKE '% | ' || $${pIdx} || ' %' THEN ${weightSub}
                ELSE 0 END)`;
            
            searchConditions.push(`(LOWER(products.search_context) ILIKE '%categorypath: ' || $${pIdx} || ' %')`);
        });
    }

    if (attributes && attributes.length > 0) {
        attributes.forEach(attr => {
            sqlParams.push(attr.toLowerCase());
            const pIdx = sqlParams.length;
            weightedScoreSql += ` + (CASE WHEN products.search_context ILIKE '%' || $${pIdx} || '%' THEN ${weightAttr} ELSE 0 END)`;
            searchConditions.push(`(products.search_context ILIKE '%' || $${pIdx} || '%')`);
        });
    }

    const whereClause = `status = 'active' AND (${searchConditions.join(' OR ')})`;

    const entityPIdx = entity ? sqlParams.indexOf(entity.toLowerCase().replace(/[^a-z0-9]/g, '')) + 1 : -1;
    // Semantic Entity Check: Use a higher threshold (0.82) specifically for the "Entity Boost" validation 
    // to distinguish valid synonyms from incorrect categories.
    const entityCheckSql = entityPIdx > 0 
        ? `(${cleanSql('p.name')} ILIKE '%' || $${entityPIdx} || '%' 
            OR ${cleanSql('p.search_context')} ILIKE '%' || $${entityPIdx} || '%'
            OR (1 - (p.embedding <=> $2::float8[]::vector)) > 0.85)`
        : `true`;

    const categoryMatchSql = categoriesTextArrayIdx > 0 
        ? `EXISTS (
            SELECT 1 FROM UNNEST($${categoriesTextArrayIdx}::text[]) c
            WHERE LOWER(p.search_context) ILIKE '%categorypath: ' || c || ' %'
               OR LOWER(p.search_context) ILIKE '% > ' || c || ' %'
               OR LOWER(p.search_context) ILIKE '% | ' || c || ' %'
               OR LOWER(p.search_context) ILIKE '%categorypath: ' || c || '|%'
               OR LOWER(p.search_context) ILIKE '%categorypath: ' || c || '>'
        )`
        : `true`;

    const boostSql = `(CASE WHEN (${categoryMatchSql}) AND (${entityCheckSql}) THEN 1.0 ELSE 0.0 END)`;

    const sql = `
        WITH keyword_results AS (
            SELECT id, name, 
                   ROW_NUMBER() OVER (ORDER BY (
                        ${weightedScoreSql} +
                        (CASE WHEN name ILIKE '%' || $1 || '%' THEN 2.0 ELSE 0 END) +
                        (CASE WHEN search_context ILIKE '%' || $1 || '%' THEN 1.0 ELSE 0 END)
                   ) DESC) as rank
            FROM products
            WHERE ${whereClause}
            ${priceFilter}
            LIMIT 100
        ),
        semantic_results AS (
            SELECT id, name,
                   ROW_NUMBER() OVER (ORDER BY embedding <=> $2::float8[]::vector) as rank
            FROM products
            WHERE status = 'active' AND embedding IS NOT NULL
              AND (1 - (embedding <=> $2::float8[]::vector)) > 0.85
            ${priceFilter}
            LIMIT 100
        )
        SELECT 
            p.id, p.name, p.price, p.description,
            (COALESCE(1.0 / ($3 + kr.rank), 0.0) + COALESCE(1.0 / ($3 + sr.rank), 0.0)) +
            -- GLOBAL BOOST (+1.0)
            ${boostSql} as rrf_score
        FROM products p
        LEFT JOIN keyword_results kr ON p.id = kr.id
        LEFT JOIN semantic_results sr ON p.id = sr.id
        WHERE kr.id IS NOT NULL OR sr.id IS NOT NULL
        ORDER BY rrf_score DESC
        LIMIT $4 OFFSET $5;
    `;

    const res = await db.query(sql, sqlParams);
    return res.rows;
}

/**
 * Hybrid search combining Trigram (Keyword) and Vector (Semantic) search.
 * Uses Reciprocal Rank Fusion (RRF) for ranking.
 */
export const hybridSearchTool = new DynamicStructuredTool({
    name: "hybrid_product_search",
    description: "Search for products using both keyword matches and semantic concepts. Handles hierarchy and attributes.",
    schema: z.object({
        query: z.string().describe("The user's search query (e.g., 'warm winter jacket')"),
        entity: z.string().optional().describe("The primary product type (e.g., 'tshirt', 'jeans', 'laptop')"),
        categories: z.array(z.string()).optional().describe("Target demographics, departments, or high-level categories (e.g., ['men', 'beauty', 'electronics'])"),
        attributes: z.array(z.string()).optional().describe("Specific attributes like color, size, brand, or material (e.g., ['blue', 'XL', 'nike', 'cotton'])"),
        limit: z.number().optional().default(5).describe("Number of results to return"),
        min_price: z.number().optional().describe("Minimum price"),
        max_price: z.number().optional().describe("Maximum price"),
    }),
    func: async ({ query, entity, categories, attributes, limit, min_price, max_price }) => {
        let finalMin = min_price;
        let finalMax = max_price;

        // Auto-extract price from query if not provided
        if (finalMax === undefined) {
            const underMatch = query.match(/(?:under|less than|below|max)\s*[$€£]?\s*(\d+(?:\.\d{2})?)/i);
            if (underMatch) finalMax = parseFloat(underMatch[1]);
        }
        if (finalMin === undefined) {
            const aboveMatch = query.match(/(?:above|more than|greater than|min|over)\s*[$€£]?\s*(\d+(?:\.\d{2})?)/i);
            if (aboveMatch) finalMin = parseFloat(aboveMatch[1]);
        }

        console.log(`[AGENT] Tool hybrid_product_search called. Query: ${query}, Entity: ${entity}, Cats: ${categories}, Attrs: ${attributes}`);
        
        try {
            const queryEmbedding = await embeddingsService.generateEmbedding(query);
            const rows = await executeHybridSearch({
                query,
                embedding: queryEmbedding,
                limit,
                min_price: finalMin,
                max_price: finalMax,
                entity,
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
