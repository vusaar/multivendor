import { processUserQuery } from '../src/services/search.agent';

const testCases = [
    { query: "top", expectedTitle: "Shirt" },
    { query: "jumper", expectedTitle: "Sweater" },
    { query: "kicks", expectedTitle: "Sneaker" },
    { query: "activewear", expectedTitle: "T-shirt" },
    { query: "apparel", expectedTitle: "Shirt" }
];

async function runSynonymTests() {
    console.log("\n🧪 BULK SYNONYM VERIFICATION");
    console.log("==================================================");

    for (const test of testCases) {
        process.stdout.write(`Testing: "${test.query}" ... `);
        try {
            const results = await processUserQuery(test.query, "synonym_test_user");
            if (results && results.length > 0) {
                const top = results[0];
                const isMatch = top.name.toLowerCase().includes(test.expectedTitle.toLowerCase());
                const status = top.rrf_score >= 75 ? "✅ VERIFIED" : "⚠️ SUGGESTION";
                
                console.log(`${status} (Top: "${top.name}", Score: ${top.rrf_score.toFixed(2)})`);
            } else {
                console.log("❌ NO RESULTS");
            }
        } catch (error: any) {
            console.log(`❌ CRASH: ${error.message}`);
        }
    }
    console.log("==================================================\n");
    process.exit(0);
}

runSynonymTests();
