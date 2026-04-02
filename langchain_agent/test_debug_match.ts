import { db } from './src/config/database';
import { executeHybridSearch } from './src/tools/vector_search.tool';
import { embeddingsService } from './src/services/embeddings.service';
require('dotenv').config();

async function run() {
    const query = 'running shoes';
    console.log(`\nDEBUGGING QUERY: "${query}"`);
    
    const embedding = await embeddingsService.generateEmbedding(query);
    
    // Simulate what the AI would send
    const params = {
        query,
        embedding,
        limit: 5,
        entity: 'running shoes',
        synonyms: ['sneaker', 'trainer', 'footwear'],
        categories: [],
        attributes: ['running']
    };

    console.log("Mocking AI Input with Synonyms:", params.synonyms);

    const results = await executeHybridSearch(params);
    console.log("\nSEARCH RESULTS FOR ID 59:");
    const sneaker = results.find((r: any) => parseInt(r.id) === 59);
    
    if (sneaker) {
        console.log(`ID: ${sneaker.id}`);
        console.log(`Name: ${sneaker.name}`);
        console.log(`RRF Score: ${sneaker.rrf_score}`);
        console.log(`Is Direct Match: ${sneaker.is_direct_match} <--- IS THIS TRUE?`);
        console.log(`Lineage: ${sneaker.category_lineage}`);
    } else {
        console.log("ID 59 not found in top 5 results.");
        // View the first result to see what's happening
        if (results.length > 0) {
           console.log("Top Result 1:", results[0].name, "Direct Match:", results[0].is_direct_match);
        }
    }
}

run().catch(console.error).finally(() => process.exit(0));
