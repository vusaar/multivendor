import { db } from "./src/config/database";
import { embeddingsService } from "./src/services/embeddings.service";

async function runStrTest() {
    const q1 = "top";
    const e1 = await embeddingsService.generateEmbedding(q1);
    const sqlParams = [`[${e1.join(',')}]`];
    
    const sql = `
        SELECT id, name, (1 - (embedding <=> $1::vector)) AS vector_score
        FROM products
        WHERE status = 'active' AND embedding IS NOT NULL
        ORDER BY vector_score DESC
        LIMIT 5;
    `;
    const res = await db.query(sql, sqlParams);
    console.log(`Top 5 products for "${q1}":`);
    console.table(res.rows);

    process.exit(0);
}

runStrTest().catch(console.error);
