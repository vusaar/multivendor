import { db } from './src/config/database';
import { embeddingsService } from './src/services/embeddings.service';

async function checkSimilarity() {
    const query = "Pizza";
    const productId = 14; 
    
    try {
        const queryEmbedding = await embeddingsService.generateEmbedding(query);
        const embeddingStr = `[${queryEmbedding.join(",")}]`;
        
        const sql = `
            SELECT id, name,
            (1 - (embedding <=> $1::vector)) as similarity
            FROM products
            WHERE id = $2;
        `;
        
        const res = await db.query(sql, [embeddingStr, productId]);
        
        if (res.rows.length > 0) {
            console.log(`Similarity between "${query}" and "${res.rows[0].name}": ${res.rows[0].similarity}`);
        } else {
            console.log(`Product ID ${productId} not found.`);
        }
    } catch (error) {
        console.error("Error checking similarity:", error);
    } finally {
        process.exit(0);
    }
}

checkSimilarity();
