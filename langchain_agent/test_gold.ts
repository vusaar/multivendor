import { processUserQuery } from './src/services/search.agent';

async function testGoldShirts() {
    try {
        const results = await processUserQuery("gold tshirts", "test_user_gold_" + Date.now());
        console.log("Returned results count:", results.length);
        if (results.length > 0) {
            console.log("Top 3 results:");
            console.log(results.slice(0, 3).map((r: any) => ({ id: r.id, name: r.name, rrf_score: r.rrf_score })));
        }
    } catch (e: any) {
        console.error(e.message);
    }
    process.exit(0);
}

testGoldShirts();
