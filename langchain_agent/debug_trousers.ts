import { processUserQuery } from './src/services/search.agent';

async function testSearchAccuracy() {
    const query = process.argv[2] || "men trousers";
    console.log(`\n🔍 TESTING SEARCH: "${query}"`);
    console.log("--------------------------------------------------");
    
    try {
        const results = await processUserQuery(query, "diagnostic_user");
        
        if (Array.isArray(results)) {
            console.log(`✅ Found ${results.length} results.`);
            results.slice(0, 10).forEach((p: any, i: number) => {
                console.log(`\n[${i + 1}] ${p.name}`);
                console.log(`    - ID: ${p.id}`);
                const rrf = parseFloat(p.rrf_score?.toString() || "0");
                const sem = parseFloat(p.semantic_score?.toString() || "0");
                console.log(`    - Score (RRF): ${rrf.toFixed(4)}`);
                console.log(`    - Semantic Score: ${sem.toFixed(4)}`);
                console.log(`    - Rank: ${p.rank}`);
                console.log(`    - Search Context: ${p.search_context?.substring(0, 100)}...`);
            });
        } else {
            console.log("❌ No array results returned:", results);
        }
    } catch (error: any) {
        console.error("❌ Error during search:", error.message);
    }
    
    process.exit(0);
}

testSearchAccuracy();
