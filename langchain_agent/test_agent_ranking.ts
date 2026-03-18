import { hybridSearchTool } from "./src/tools/vector_search.tool";

async function runTest() {
    const query = "white tshirt";
    console.log(`Testing Agent Hybrid Search for: "${query}"`);

    try {
        const result = await hybridSearchTool.func({ query, limit: 10 });
        const products = JSON.parse(result);

        if (Array.isArray(products)) {
            console.log("RRF Scoreboard:");
            products.forEach((p: any, i: number) => {
                console.log(`${i + 1}. [ID: ${p.id}] ${p.name} - Score: ${p.rrf_score}`);
            });
        } else {
            console.log("Result:", result);
        }
    } catch (e: any) {
        console.error("Test failed:", e.message);
    }
}

runTest();
