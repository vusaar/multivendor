import { processUserQuery } from './src/services/search.agent';
require('dotenv').config();

async function testGolf() {
    const query = "golf tshirts";
    const userId = "test_user_golf_" + Date.now();
    console.log(`\n=== Testing Query: "${query}" (User: ${userId}) ===`);
    
    const results: any = await processUserQuery(query, userId);
    
    if (Array.isArray(results)) {
        console.log(`Total results found: ${results.length}`);
        
        if (results.length > 0 && results[0].id === 'AI_MESSAGE') {
            console.log("AI Message Result:", results[0].text);
            return;
        }

        const verified = results.filter((p: any) => parseFloat(p.rrf_score || "0") >= 180.0);
        const suggested = results.filter((p: any) => parseFloat(p.rrf_score || "0") < 180.0);

        console.log(`\nVerified Results (${verified.length}):`);
        verified.forEach((p: any, i: number) => {
            console.log(`${i + 1}. ${p.name} (RRF: ${p.rrf_score}) [ID: ${p.id}]`);
        });

        console.log(`\nSuggested Results (${suggested.length}):`);
        suggested.slice(0, 5).forEach((p: any, i: number) => {
            console.log(`${i + 1}. ${p.name} (RRF: ${p.rrf_score}) [ID: ${p.id}]`);
        });
    } else {
        console.log("Search failed or returned non-array results:", results);
    }
}

testGolf().catch(console.error).finally(() => process.exit(0));
