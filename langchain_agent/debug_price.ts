import { processUserQuery } from './src/services/search.agent';

async function debugPriceFilter() {
    const query = "ladies tops for less than $10";
    console.log(`Debugging Query: "${query}"`);
    try {
        const results = await processUserQuery(query);
        console.log("Final Results:", JSON.stringify(results, null, 2));
    } catch (error: any) {
        console.error("Debug Error:", error.message);
    }
}

debugPriceFilter().then(() => process.exit(0));
