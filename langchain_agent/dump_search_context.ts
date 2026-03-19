import { db } from './src/config/database';
import { embeddingsService } from './src/services/embeddings.service';
import dotenv from 'dotenv';
dotenv.config();

async function dump() {
    const query = 'shoes';
    const queryEmbedding = await embeddingsService.generateEmbedding(query);
    const embeddingStr = `[${queryEmbedding.join(",")}]`;
    const k = 40;

    const sql = `
        WITH keyword_results AS (
            SELECT id, name, 
                   similarity(name, $1) as name_sim,
                   similarity(search_context, $1) as context_sim,
            FROM products
            WHERE status = 'active' AND (similarity(name, $1) > 0.4 OR (name ILIKE '%' || $1 || '%') OR (search_context ILIKE '%' || $1 || '%'))
            LIMIT 50
        ),
        semantic_results AS (
            SELECT id, name,
                   (1 - (embedding <=> $2::vector)) as sem_sim,
                   ROW_NUMBER() OVER (ORDER BY embedding <=> $2::vector) as rank
            FROM products
            WHERE status = 'active' AND embedding IS NOT NULL
            LIMIT 50
        )
        SELECT 
            p.id, p.name, p.search_context,
            kr.name_sim, kr.context_sim, kr.rank as k_rank,
            sr.sem_sim, sr.rank as s_rank,
            COALESCE(1.0 / ($3 + kr.rank), 0.0) + COALESCE(1.0 / ($3 + sr.rank), 0.0) as rrf_score
        FROM products p
        LEFT JOIN keyword_results kr ON p.id = kr.id
        LEFT JOIN semantic_results sr ON p.id = sr.id
        WHERE kr.id IS NOT NULL OR sr.id IS NOT NULL OR p.id = 4
        ORDER BY rrf_score DESC
        LIMIT 20;
    `;

    const res = await db.query(sql, [query, embeddingStr, k]);
    const fs = require('fs');
    fs.writeFileSync('relevance_dump_final.json', JSON.stringify(res.rows, null, 2));
    console.log("Dump written to relevance_dump_final.json");
}

dump();
