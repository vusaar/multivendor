import { db } from './src/config/database';
import { executeHybridSearch } from './src/tools/vector_search.tool';
import { embeddingsService } from './src/services/embeddings.service';
require('dotenv').config();

async function run() {
    const query = 'ladies running shoes';
    console.log(`\nSEARCH: "${query}"`);
    
    const embedding = await embeddingsService.generateEmbedding(query);
    
    // Use the actual search tool logic
    const results = await executeHybridSearch({
        query,
        embedding,
        limit: 10,
        entity: 'running shoes',
        synonyms: ['sneaker', 'trainer'],
        categories: ['women'],
        attributes: ['running']
    });

    console.log("\nREAL DATABASE RESULTS (IDs up to 60):");
    if (results.length === 0) {
        console.log("No results found.");
    } else {
        results.forEach((r: any, i: number) => {
            console.log(`${i+1}. [ID ${r.id}] ${r.name} - Score: ${r.rrf_score.toFixed(2)} - Lineage: ${r.category_lineage}`);
        });
    }
}

run().catch(console.error).finally(() => process.exit(0));
