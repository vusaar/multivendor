import { executeHybridSearch } from './src/tools/vector_search.tool';
import { embeddingsService } from './src/services/embeddings.service';
require('dotenv').config();

async function debugScore() {
    const query = "golf tshirts";
    const embedding = await embeddingsService.generateEmbedding(query);
    
    // According to Step 2225 logs
    const results = await executeHybridSearch({
        query,
        embedding,
        limit: 5,
        entity: "tshirt",
        synonyms: ["t-shirt", "shirt", "golf"],
        attributes: ["golf"]
    });

    results.forEach(r => {
        console.log(`\n${r.name} [ID: ${r.id}]`);
        console.log(`RRF: ${r.rrf_score}`);
    });
}

debugScore().catch(console.error).finally(() => process.exit(0));
