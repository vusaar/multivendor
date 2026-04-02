import { processUserQuery } from './src/services/search.agent';
require('dotenv').config();

async function run() {
    const query = 'running shoes';
    console.log(`\nAGENT METADATA AUDIT: "${query}"`);
    
    const results = await processUserQuery(query, 'debugger_user_metadata');

    console.log("\nRAW OBJECT AUDIT (First Result):");
    if (results && results.length > 0) {
        const p = results[0];
        console.log("Keys found in result object:", Object.keys(p));
        console.log("is_direct_match value:", p.is_direct_match);
        console.log("rrf_score value:", p.rrf_score);
        console.log("score value (mapped):", p.score);
    } else {
        console.log("No results returned.");
    }
}

run().catch(console.error).finally(() => process.exit(0));
