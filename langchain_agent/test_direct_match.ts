import { productSearchService } from './src/services/product.search.service';
require('dotenv').config();

async function run() {
    const query = 'tshirt';
    console.log(`\nPRODUCTION SEARCH: "${query}"`);
    
    // This now calls processUserQuery internally (v1.9)
    const results = await productSearchService.search(query, 1, 'debugger_user_2');

    console.log("\nFINAL PRODUCTION RESULTS:");
    results.data.forEach((p: any, i: number) => {
        console.log(`${i+1}. [ID ${p.id}] ${p.name} (Score: ${p.rrf_score}) - Direct: ${p.is_direct_match}`);
    });
}

run().catch(console.error).finally(() => process.exit(0));
