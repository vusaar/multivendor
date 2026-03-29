import { db } from './src/config/database';
import { embeddingsService } from './src/services/embeddings.service';
import dotenv from 'dotenv';
import { executeHybridSearch } from './src/tools/vector_search.tool';

dotenv.config();

async function run() {
    const query = "tshirt";
    const embedding = await embeddingsService.generateEmbedding(query);
    
    // We want to see EVERYTHING in the candidates
    const results = await executeHybridSearch({
        query,
        embedding,
        limit: 100,
        entity: "t-shirt",
        synonyms: ["shirt", "tee", "top", "tshirt"]
    });

    console.log(`Found ${results.length} total results.`);
    results.forEach((r, i) => {
        if (i < 40) {
            console.log(`${i+1}. [ID: ${r.id}] ${r.name} - Score: ${r.rrf_score}`);
        }
    });

    process.exit(0);
}
run();
