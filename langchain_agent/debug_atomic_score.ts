import { db } from './src/config/database';
import dotenv from 'dotenv';
dotenv.config();

async function run() {
    const query = 'shoes';
    const entity = 'shoe';
    const product_id = 45; // Using the top Shirt ID

    const sql = `
        WITH config AS (
            SELECT set_config('pg_trgm.word_similarity_threshold', '0.5', true),
                   set_config('pg_trgm.strict_word_similarity_threshold', '0.5', true)
        )
        SELECT 
            name,
            (CASE WHEN LOWER(name) = $1 THEN 150.0 ELSE 0.0 END) as literal_name,
            (CASE WHEN $1 <% LOWER(name) THEN word_similarity($1, LOWER(name)) * 80.0 ELSE 0.0 END) as fuzzy_name,
            (CASE WHEN $1 <% LOWER(description) THEN word_similarity($1, LOWER(description)) * 40.0 ELSE 0.0 END) as fuzzy_desc,
            (CASE WHEN $2 <<% LOWER(name) THEN strict_word_similarity($2, LOWER(name)) * 200.0 ELSE 0.0 END) as entity_name,
            (CASE WHEN $2 <% LOWER(search_context) THEN word_similarity($2, LOWER(search_context)) * 80.0 ELSE 0.0 END) as entity_context,
            (CASE WHEN $2 <% LOWER(description) THEN word_similarity($2, LOWER(description)) * 40.0 ELSE 0.0 END) as entity_desc,
            (CASE WHEN LOWER(name) = $2 THEN 50.0 ELSE 0.0 END) as entity_exact
        FROM products, config
        WHERE id = $3;
    `;

    const res = await db.query(sql, [query, entity, product_id]);
    console.log(JSON.stringify(res.rows[0], null, 2));
}

run().catch(console.error).finally(() => process.exit(0));
