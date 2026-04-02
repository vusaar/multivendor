import { executeHybridSearch } from './src/tools/vector_search.tool';
import { embeddingsService } from './src/services/embeddings.service';
require('dotenv').config();

async function run() {
    const query = 'ladies running shoes';
    const target_category_slug = 'women-sneakers';
    console.log(`\nFIELD AUDIT: "${query}"`);
    
    const embedding = await embeddingsService.generateEmbedding(query);
    const results = await executeHybridSearch({
        query,
        embedding,
        limit: 1,
        target_category_slug,
        categories: ['women']
    });

    if (results.length > 0) {
        console.log("\nRAW OBJECT AUDIT:");
        console.log("Keys found:", Object.keys(results[0]));
        console.log("Values for first product:", JSON.stringify(results[0], null, 2));
    } else {
        console.log("No database results.");
    }
}

run().catch(console.error).finally(() => process.exit(0));
