import { processUserQuery } from './src/services/search.agent';

async function verifyStrictFilter() {
    const query = "Size Small under 50";
    console.log(`Verifying Query: "${query}"`);
    try {
        const results = await processUserQuery(query);
        console.log("Raw Response JSON:", JSON.stringify(results, null, 2));
    } catch (error: any) {
        console.error("Verification Error:", error.message);
    }
}

// Wait 10 seconds to avoid rate limits
console.log("Waiting 10s...");
setTimeout(() => {
    verifyStrictFilter().then(() => process.exit(0));
}, 10000);
