import { processUserQuery } from '../src/services/search.agent';

async function testQuery() {
    const query = process.argv[2] || "blue adidas running shoes";
    console.log(`\n🔍 TESTING QUERY: "${query}"`);
    try {
        const results = await processUserQuery(query, "test_user");
        if (results && results.products && results.products.length > 0) {
            results.products.forEach((p: any, i: number) => {
                console.log(`[${i+1}] ID: ${p.id} | NAME: ${p.name} | SCORE: ${p.rrf_score.toFixed(2)}`);
            });
        } else {
            console.log("❌ NO RESULTS FOUND");
            console.log("Results Object:", JSON.stringify(results, null, 2));
        }
    } catch (error: any) {
        console.error("ERROR:", error);
    }
    process.exit(0);
}

testQuery();
