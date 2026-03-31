import { embeddingsService } from './src/services/embeddings.service'; 
import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const query = 'fine knit';
    const emb = await embeddingsService.generateEmbedding(query);
    const embStr = '[' + emb.join(',') + ']';
    const r = await db.query("SELECT id, name, (1 - (embedding <=> $1::vector)) as sim FROM products WHERE name ILIKE '%Fine-knit Blouse%' LIMIT 5", [embStr]);
    console.log(r.rows);
} 
run().catch(console.error).finally(() => process.exit(0));
