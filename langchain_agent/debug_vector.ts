import { db } from './src/config/database';
import { embeddingsService } from './src/services/embeddings.service';
import dotenv from 'dotenv';

dotenv.config();

async function checkSimilarity(query: string, targetId: number) {
    console.log(`\n=== CHECKING SIMILARITY: "${query}" vs Product ${targetId} ===`);
    try {
        const queryEmbedding = await embeddingsService.generateEmbedding(query);
        const embeddingStr = `[${queryEmbedding.join(',')}]`;

        const sql = `
            SELECT id, name, (1 - (embedding <=> $1::vector)) AS vector_score
            FROM products
            WHERE id = $2;
        `;
        const res = await db.query(sql, [embeddingStr, targetId]);
        
        if (res.rows.length === 0) {
            console.log(`❌ Product ${targetId} not found.`);
            return;
        }

        const row = res.rows[0];
        console.log(`Product: ${row.name}`);
        console.log(`Vector Score: ${row.vector_score}`);
        console.log(`Threshold 0.74: ${row.vector_score > 0.74 ? '✅ PASS' : '❌ FAIL'}`);
    } catch (error: any) {
        console.error("Error:", error.message);
    }
}

async function run() {
    await checkSimilarity("shoes under 50", 59);
    await checkSimilarity("shoe", 59);
    await checkSimilarity("tshirt", 15);
    await checkSimilarity("Mens white shirt", 60);
    await checkSimilarity("eyeshadow", 2);
    process.exit(0);
}

run();
