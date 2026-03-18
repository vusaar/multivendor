import { db } from './src/config/database';
import { embeddingsService } from './src/services/embeddings.service';
import dotenv from 'dotenv';

dotenv.config();

async function debugSimilarity() {
    const query = "electronics";
    console.log(`Testing similarity for: "${query}"`);

    try {
        const queryEmbedding = await embeddingsService.generateEmbedding(query);
        const qStr = `[${queryEmbedding.join(',')}]`;

        const res = await db.query(`
            SELECT id, name, 
                   (1 - (embedding <=> $1::vector)) as similarity
            FROM products
            WHERE embedding IS NOT NULL
            ORDER BY similarity DESC
            LIMIT 10;
        `, [qStr]);

        console.log("Top 10 Semantic Matches:");
        res.rows.forEach(r => {
            console.log(`${r.id}. ${r.name} - Similarity: ${r.similarity}`);
        });

        process.exit(0);
    } catch (error) {
        console.error("Test failed:", error);
        process.exit(1);
    }
}

debugSimilarity();
