import { processUserQuery } from './src/services/search.agent';

const sleep = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

async function runTests() {
    const testQueries = [
        "products under 20 dollars",
        "blue shirts size small",
        "mens t-shirt size XL under 50",
        "White sneakers size 42",
        "black hoodie under 30"
    ];

    console.log("--- Starting Search Agent Filter Tests (with 5s delay) ---\n");

    for (const query of testQueries) {
        console.log(`Query: "${query}"`);
        try {
            const startTime = Date.now();
            const results = await processUserQuery(query);
            const duration = Date.now() - startTime;

            console.log(`Response Time: ${duration}ms`);
            if (Array.isArray(results)) {
                console.log(`Found ${results.length} results.`);
                if (results.length > 0) {
                    console.log("Top 2 results:");
                    results.slice(0, 2).forEach(r => {
                        console.log(` - ID: ${r.id}, Name: ${r.name}, Price: ${r.price || 'N/A'}`);
                    });
                }
            } else {
                console.log("Response:", JSON.stringify(results, null, 2));
            }
        } catch (error: any) {
            console.error(`Error processing query "${query}":`, error.message);
        }
        console.log("\n------------------------------------------\n");
        await sleep(5000); // Wait 5 seconds between queries to avoid rate limits
    }
}

runTests().then(() => {
    console.log("Tests completed.");
    process.exit(0);
}).catch(err => {
    console.error("Test Suite Failed:", err);
    process.exit(1);
});
