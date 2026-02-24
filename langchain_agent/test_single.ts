import { processUserQuery } from './src/services/search.agent';

async function runSingleTest() {
    const query = "products under 20 dollars";
    console.log(`Query: "${query}"`);
    try {
        const results = await processUserQuery(query);
        console.log("Final Results Count:", Array.isArray(results) ? results.length : "Not an array");
    } catch (error: any) {
        console.error("Test Error:", error.message);
    }
}

runSingleTest().then(() => process.exit(0));
