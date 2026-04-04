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
    target_category_slug?: string;
}) {
    const { query, embedding, limit, offset = 0, min_price, max_price, entity, synonyms, categories, attributes, target_category_slug } = params;
    
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
    const DESC_THRESHOLD = '0.5';
    
    // 0. Literal Query Priority (+500 Entire, +300 Fragment)
    console.log(`[DEBUG] executeHybridSearch V4.5: Fragment Reward Active`);
    sqlParams.push(query.toLowerCase().trim());
    const qIdx = sqlParams.length;
    precisionScoreSql += ` + (CASE 
                                   WHEN regexp_replace(LOWER(P_NAME_REF), '[^a-z0-9]', '', 'g') = regexp_replace($${qIdx}, '[^a-z0-9]', '', 'g') THEN 500.0 
                                   WHEN regexp_replace(LOWER(P_NAME_REF), '[^a-z0-9]', '', 'g') ILIKE '%' || regexp_replace($${qIdx}, '[^a-z0-9]', '', 'g') || '%' THEN 300.0
                                   ELSE 0.0 END)`;
    

    // 2. Entity Match (+300)
    let eIdx = 0;
    if (entity) {
        sqlParams.push(entity.toLowerCase().trim());
        eIdx = sqlParams.length;
        // CANONICAL SIMILARITY (V4.2) solves compounding (roundneck vs round-neck)
        // We use word_similarity on stripped strings to find the 'word' inside the name
        // Added LOWER() to the literal param side for 100% consistency
        precisionScoreSql += ` + (CASE WHEN strict_word_similarity(LOWER($${eIdx}), LOWER(P_NAME_REF)) >= 0.5 THEN 300.0 
                                       WHEN word_similarity(regexp_replace(LOWER($${eIdx}), '[^a-z0-9]', '', 'g'), regexp_replace(LOWER(P_NAME_REF), '[^a-z0-9]', '', 'g')) >= 0.7 THEN 250.0
                                       WHEN word_similarity(LOWER($${eIdx}), LOWER(P_NAME_REF)) >= 0.4 THEN 150.0
                                       ELSE 0.0 END)`;
    }

    // 2. Taxonomic & Demographic Branch Scoring (v3.0 - Pure Graph)
    let targetIdx = 0;
    if (target_category_slug) {
        sqlParams.push(target_category_slug);
        targetIdx = sqlParams.length;
    }

    const isWomenQuery = categories?.some(c => ['women', 'ladies', 'girls'].includes(c.toLowerCase())) || target_category_slug?.startsWith('women-');
    const isMenQuery = categories?.some(c => ['men', 'gents', 'boys', 'mans'].includes(c.toLowerCase())) || target_category_slug?.startsWith('men-');

    console.log(`[TSD v3.0] Pure Graph Mode: isWomen=${isWomenQuery}, isMen=${isMenQuery} (Slug: ${target_category_slug})`);

    // 2b. Specific Sub-Category Match (+100 per specific match)
    if (categories && categories.length > 0) {
        categories.forEach(cat => {
            const lowCat = cat.toLowerCase();
            if (['women', 'ladies', 'girls', 'men', 'gents', 'boys', 'mans'].includes(lowCat)) return;
            sqlParams.push(lowCat);
            const pIdx = sqlParams.length;
            precisionScoreSql += ` + (CASE WHEN strict_word_similarity($${pIdx}, LOWER(search_context)) >= 0.5 THEN 100.0 ELSE 0.0 END)`;
        });
    }

    // 2c. Synonym Integration (+200 if matched)
    if (synonyms && synonyms.length > 0) {
        synonyms.forEach(syn => {
            sqlParams.push(syn.toLowerCase().trim());
            const sIdx = sqlParams.length;
            precisionScoreSql += ` + (CASE WHEN strict_word_similarity(LOWER($${sIdx}), LOWER(P_NAME_REF)) >= 0.5 THEN 200.0 
                                           WHEN word_similarity(regexp_replace(LOWER($${sIdx}), '[^a-z0-9]', '', 'g'), regexp_replace(LOWER(P_NAME_REF), '[^a-z0-9]', '', 'g')) >= 0.8 THEN 150.0
                                           WHEN word_similarity(LOWER($${sIdx}), LOWER(search_context)) >= 0.5 THEN 100.0
                                           ELSE 0.0 END)`;
        });
    }

    // 3. Attribute Precision (+100)
    if (attributes && attributes.length > 0) {
        attributes.forEach(attr => {
            sqlParams.push(attr.toLowerCase().trim());
            const aIdx = sqlParams.length;
            precisionScoreSql += ` + (CASE WHEN word_similarity($${aIdx}, LOWER(search_context)) >= 0.5 THEN 100.0 
                                           WHEN word_similarity($${aIdx}, LOWER(P_NAME_REF)) >= 0.5 THEN 100.0
                                           ELSE 0.0 END)`;
        });
    }

    const finalSql = `
        WITH RECURSIVE category_hierarchy AS (
            -- Base case: roots
            SELECT id, name, slug, synonyms, id as root_id, name as root_name, 0 as level, ARRAY[id] as ancestor_ids
            FROM categories
            WHERE parent_id IS NULL
            UNION ALL
            -- Recursive step: build integer graph path
            SELECT c.id, c.name, c.slug, c.synonyms, ch.root_id, ch.root_name, ch.level + 1, ch.ancestor_ids || c.id
            FROM categories c
            JOIN category_hierarchy ch ON c.parent_id = ch.id
        ),
        target_info AS (
            -- Identify our target category node in the graph
            SELECT id, root_id, level, ancestor_ids
            FROM category_hierarchy
            WHERE slug = ${targetIdx > 0 ? `$${targetIdx}` : "''"}
            LIMIT 1
        ),
        raw_candidates AS (
            SELECT p.id, p.name, p.price, p.description, p.search_context, p.vendor_id, p.category_id, p.status,
                   (1 - (p.embedding <=> $1::vector)) AS vector_score,
                    ch.root_id as demographic_root_id,
                   ch.root_name as demographic_root,
                   ch.level as category_level,
                   ch.ancestor_ids as category_ancestors,
                   ch.name as category_name,
                   ch.synonyms as category_synonyms,
                   (SELECT pi.image FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.id ASC LIMIT 1) as image_path
            FROM products p
            LEFT JOIN category_hierarchy ch ON p.category_id = ch.id
            WHERE p.status = 'active' AND p.embedding IS NOT NULL
              -- RECALL RECOVERY (V4.2): Lowered gate to 0.40 to account for enriched (noisier) search context
              AND (1 - (p.embedding <=> $1::vector)) > 0.40
            ${priceFilter}
            ORDER BY vector_score DESC
            LIMIT 200
        ),
        scored_candidates AS (
            SELECT r.*,
                   -- 1. Direct Category Match Reward (TSD v4.0)
                   (CASE 
                        WHEN strict_word_similarity(LOWER($${qIdx}), LOWER(r.category_name)) >= 0.7 THEN 600.0
                        WHEN word_similarity(LOWER($${qIdx}), LOWER(r.category_name)) >= 0.5 THEN 400.0
                        WHEN r.category_synonyms IS NOT NULL AND EXISTS (
                            SELECT 1 FROM jsonb_array_elements_text(r.category_synonyms) as s 
                            WHERE strict_word_similarity(LOWER($${qIdx}), LOWER(s)) >= 0.7
                        ) THEN 500.0
                        WHEN r.category_synonyms IS NOT NULL AND EXISTS (
                            SELECT 1 FROM jsonb_array_elements_text(r.category_synonyms) as s 
                            WHERE word_similarity(LOWER($${qIdx}), LOWER(s)) >= 0.5
                        ) THEN 300.0
                        ELSE 0.0
                   END) as category_match_reward,
                   -- 2. Taxonomic Graph Reward
                   (CASE 
                        WHEN ti.id IS NOT NULL AND (r.category_id = ti.id) THEN 500.0
                        WHEN ti.id IS NOT NULL AND (ti.id = ANY(r.category_ancestors)) THEN (350.0 * POWER(0.8, (r.category_level - ti.level)))
                        WHEN ti.id IS NOT NULL AND (r.category_id = ANY(ti.ancestor_ids)) THEN (200.0 * POWER(0.7, (ti.level - r.category_level)))
                        ELSE 0.0 
                   END) as taxonomy_reward,
                   (CASE
                        WHEN ti.id IS NOT NULL AND r.demographic_root_id != ti.root_id THEN -2000.0
                        WHEN ${isWomenQuery ? 'TRUE' : 'FALSE'} AND r.demographic_root ILIKE '%men%' AND r.demographic_root NOT ILIKE '%women%' THEN -2000.0
                        WHEN ${isMenQuery ? 'TRUE' : 'FALSE'} AND r.demographic_root ILIKE '%women%' THEN -2000.0
                        ELSE 0.0
                   END) as branch_penalty,
                   (CASE
                        WHEN ${isWomenQuery ? 'TRUE' : 'FALSE'} AND r.demographic_root ILIKE '%women%' THEN 200.0
                        WHEN ${isMenQuery ? 'TRUE' : 'FALSE'} AND r.demographic_root ILIKE '%men%' THEN 200.0
                        ELSE 0.0
                   END) as branch_reward,
                   (${precisionScoreSql.replace(/P_NAME_REF/g, 'r.name')}) as precision_score
            FROM raw_candidates r
            LEFT JOIN target_info ti ON TRUE
        )
        SELECT 
            id, name, price, description, vendor_id, category_id, status, search_context, demographic_root, image_path,
            COUNT(*) OVER() as total_count,
            (
                (vector_score * 10) + 
                precision_score +
                category_match_reward +
                taxonomy_reward +
                branch_penalty +
                branch_reward
            ) AS rrf_score,
            (CASE WHEN (category_match_reward >= 500.0) OR (taxonomy_reward >= 350.0) OR 
            (precision_score >= 180.0) OR 
            (LOWER(r.name) ILIKE '%' || $${qIdx} || '%') OR
            (regexp_replace(LOWER(r.name), '[^a-z0-9]', '', 'g') ILIKE '%' || regexp_replace(LOWER($${qIdx}), '[^a-z0-9]', '', 'g') || '%')
            THEN TRUE ELSE FALSE END) as is_direct_match
        FROM scored_candidates r
        WHERE (
            -- NOISE REJECTION (V4.2): Purely semantic matches without lexical/taxonomic proof must be ABSOLUTE (0.99+)
            (r.category_match_reward > 0) OR
            (r.taxonomy_reward > 0) OR
            (r.precision_score >= 100.0) OR
            ((r.vector_score * 10) >= 9.9)
        )
        ORDER BY rrf_score DESC
        LIMIT $2 OFFSET $3;
    `;

    console.log(`[DB] EXECUTING SQL V3 (Graph TSD):`, finalSql);
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
        target_category_slug: z.string().optional().describe("The specific category slug identified from the taxonomy guide (e.g., 'men-sneakers')."),
        limit: z.number().optional().default(5).describe("Number of results to return"),
        min_price: z.number().optional().describe("Minimum price"),
        max_price: z.number().optional().describe("Maximum price"),
        brand: z.string().optional().describe("Specific brand name (e.g. 'adidas', 'nike')"),
    }),
    func: async ({ query, entity, synonyms, categories, attributes, limit, min_price, max_price, target_category_slug, brand }) => {
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

        // TSD v4.7: Handle brand aliasing/hallucinations
        const finalAttributes = [...(attributes || [])];
        if (brand && !finalAttributes.includes(brand)) {
            finalAttributes.push(brand);
        }

        try {
            const queryEmbedding = await embeddingsService.generateEmbedding(query);
            const rows = await executeHybridSearch({
                query,
                entity,
                synonyms,
                categories,
                attributes: finalAttributes,
                embedding: queryEmbedding,
                limit,
                min_price: finalMin,
                max_price: finalMax,
                target_category_slug: (target_category_slug as any)
            });

            if (rows.length === 0) {
                return JSON.stringify({ status: "no_results", message: `No products found for "${query}"` });
            }

            // Post-process to include full image URL
            const processedRows = rows.map((row: any) => ({
                ...row,
                image: row.image_path ? `https://store.eyamisolutions.co.zw/storage/${row.image_path}` : null
            }));

            return JSON.stringify(processedRows);
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
                ORDER BY similarity DESC
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

