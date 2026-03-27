import { embeddingsService } from '../src/services/embeddings.service';
import { db } from '../config/database';

async function checkSimilarity() {
    const query = 'running shoes';
    const productId = 59;

    try {
        const queryEmbedding = await embeddingsService.generateEmbedding(query);
        const queryEmbeddingStr = `[${queryEmbedding.join(',')}]`;

        const sql = `
            SELECT id, name, 
                   (1 - (embedding <=> $1::vector)) AS similarity
            FROM products 
            WHERE id = $2;
        `;

        const res = await db.query(sql, [queryEmbeddingStr, productId]);
        
        if (res.rows.length > 0) {
            const item = res.rows[0];
            console.log(`Product: ${item.name}`);
            console.log(`Similarity to "${query}": ${item.similarity}`);
            console.log(`Current threshold: 0.74`);
        } else {
            console.log("Product not found.");
        }
    } catch (error: any) {
        console.error("Error:", error.message);
    }
    process.exit(0);
}

checkSimilarity();
