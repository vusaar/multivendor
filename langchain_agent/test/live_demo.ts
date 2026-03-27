import { processUserQuery } from '../src/services/search.agent';

const demoCases = [
    { query: "looking for kicks", reason: "Testing 'kicks' -> 'sneaker' slang" },
    { query: "blue apparel", reason: "Testing attribute ('blue') + generic synonym ('apparel' -> 'shirt')" },
    { query: "formal wear", reason: "Testing complex intent mapping" },
    { query: "something for a cold day", reason: "Testing semantic intent -> Sweater" }
];

async function runDemo() {
    console.log("\n🚀 LIVE SEARCH DEMO: HIGH-PRECISION RESULTS");
    console.log("==================================================");

    for (const demo of demoCases) {
        console.log(`\n🔍 QUERY: "${demo.query}" (${demo.reason})`);
        try {
            const results = await processUserQuery(demo.query, "demo_user");
            if (results && results.length > 0) {
                const top = results[0];
                const type = top.rrf_score >= 75 ? "✅ VERIFIED MATCH" : "⚠️ SUGGESTION";
                console.log(`${type}: "${top.name}"`);
                console.log(`Score: ${top.rrf_score.toFixed(2)} | Price: ${top.price}`);
            } else {
                console.log("❌ NO RESULTS FOUND");
            }
        } catch (error: any) {
            console.log(`❌ ERROR: ${error.message}`);
        }
    }
    console.log("\n==================================================");
    process.exit(0);
}

runDemo();
