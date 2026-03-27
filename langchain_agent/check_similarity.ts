import { embeddingsService } from './src/services/embeddings.service';
import { db } from './src/config/database';

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
            WHERE id IN (59, 35);
        `;

        const res = await db.query(sql, [queryEmbeddingStr]);
        
        res.rows.forEach(item => {
            console.log(`Product: ${item.name} (ID: ${item.id})`);
            console.log(`Similarity to "${query}": ${item.similarity}`);
            console.log(`Score (Sim * 10): ${item.similarity * 10}`);
            console.log("---");
        });
    } catch (error: any) {
        console.error("Error:", error.message);
    }
    process.exit(0);
}

checkSimilarity();
