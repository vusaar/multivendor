
import { Client } from 'pg';
import dotenv from 'dotenv';
import path from 'path';

dotenv.config({ path: path.join(__dirname, '../.env') });

async function debugSql() {
    const client = new Client({
        connectionString: process.env.DATABASE_URL
    });
    await client.connect();

    console.log("--- Testing TSD v6.0 SQL ---");
    
    // Mock parameters for "men trousers"
    const query = "men trousers";
    const embedding = new Array(1536).fill(0); // Mock embedding
    const target_category_slug = "men-clothing"; // Hypothetical slug
    const isWomenQuery = false;
    const isMenQuery = true;
    const sqlParams = [embedding, 20, 0, target_category_slug, query];
    const qIdx = 5;
    const targetIdx = 4;
    const precisionScoreSql = "0.0 + (CASE WHEN LOWER(P_NAME_REF) ILIKE '%trousers%' THEN 500.0 ELSE 0.0 END)";

    const finalSql = `
        WITH RECURSIVE category_hierarchy AS (
            SELECT id, name, id as root_id, name as root_name, name::text as full_path
            FROM categories
            WHERE parent_id IS NULL
            UNION ALL
            SELECT c.id, c.name, ch.root_id, ch.root_name, (ch.full_path || ' > ' || c.name)
            FROM categories c
            JOIN category_hierarchy ch ON c.parent_id = ch.id
        ),
        semantic_candidates AS (
            SELECT 
                p_id, p_name, price, description, search_context, vendor_id, category_id, status,
                vector_score, demographic_root, category_lineage, c_name,
                taxonomy_reward, branch_penalty, branch_reward, precision_score
            FROM (
                SELECT p.id as p_id, p.name as p_name, p.price, p.description, p.search_context, p.vendor_id, p.category_id, p.status,
                       (1 - (p.embedding <=> $1::vector)) AS vector_score,
                       ch.root_name as demographic_root,
                       ch.full_path as category_lineage,
                       ch.name as c_name,
                       (CASE 
                            WHEN ts.target_id IS NOT NULL AND (p.category_id = ts.target_id) THEN 500.0
                            WHEN ts.target_id IS NOT NULL AND (ch.full_path ILIKE '%' || ts.t_name || '%') THEN 300.0
                            WHEN ts.target_id IS NOT NULL AND (ts.t_full_path ILIKE '%' || ch.name || '%') THEN 250.0
                            ELSE 0.0 
                       END) as taxonomy_reward,
                       (CASE
                            WHEN ${isWomenQuery ? 'TRUE' : 'FALSE'} AND ch.root_name ILIKE '%men%' AND ch.root_name NOT ILIKE '%women%' THEN -1000.0
                            WHEN ${isMenQuery ? 'TRUE' : 'FALSE'} AND ch.root_name ILIKE '%women%' THEN -1000.0
                            ELSE 0.0
                       END) as branch_penalty,
                       (CASE
                            WHEN ${isWomenQuery ? 'TRUE' : 'FALSE'} AND ch.root_name ILIKE '%women%' THEN 200.0
                            WHEN ${isMenQuery ? 'TRUE' : 'FALSE'} AND ch.root_name ILIKE '%men%' THEN 200.0
                            ELSE 0.0
                       END) as branch_reward,
                       (${precisionScoreSql.replace(/P_NAME_REF/g, 'p.name')}) as precision_score
                FROM products p
                LEFT JOIN category_hierarchy ch ON p.category_id = ch.id
                LEFT JOIN (
                    SELECT ch2.id as target_id, ch2.name as t_name, ch2.full_path as t_full_path 
                    FROM categories c2 
                    JOIN category_hierarchy ch2 ON c2.id = ch2.id 
                    WHERE c2.slug = $4
                ) ts ON TRUE
                WHERE p.status = 'active' AND p.embedding IS NOT NULL
                  AND (1 - (p.embedding <=> $1::vector)) > 0.70 
            ) sub
            WHERE (
                (taxonomy_reward > 0) OR
                (precision_score > 0) OR
                ((vector_score * 10) >= 9.8)
            )
            ORDER BY vector_score DESC
            LIMIT 200
        )
        SELECT 
            p_id as id, p_name as name, price, description, vendor_id, category_id, status, search_context, category_lineage, demographic_root,
            (
                (vector_score * 10) + 
                (${precisionScoreSql.replace(/P_NAME_REF/g, 'sub.p_name')}) +
                taxonomy_reward +
                branch_penalty +
                branch_reward
            ) AS rrf_score,
            (CASE WHEN taxonomy_reward >= 300.0 OR (LOWER(p_name) ILIKE '%' || $5 || '%') THEN TRUE ELSE FALSE END) as is_direct_match
        FROM semantic_candidates sub
        ORDER BY rrf_score DESC
        LIMIT $2 OFFSET $3;
    `;

    try {
        const res = await client.query(finalSql, sqlParams);
        console.log(`Success! Found ${res.rows.length} rows.`);
        if (res.rows.length > 0) console.log("Top result:", res.rows[0]);
    } catch (err: any) {
        console.error("SQL Error:", err.message);
    } finally {
        await client.end();
    }
}

debugSql();
