import { processUserQuery } from './src/services/search.agent';

async function verifyFix() {
    const query = "blue shirts size small under 50";
    console.log(`Verifying Query: "${query}"`);
    try {
        const results = await processUserQuery(query);
        console.log("Results:", JSON.stringify(results, null, 2));
    } catch (error: any) {
        console.error("Verification Error:", error.message);
    }
}

// Wait 10 seconds before starting to avoid pending rate limits
console.log("Waiting 10s for rate limit cool-down...");
setTimeout(() => {
    verifyFix().then(() => process.exit(0));
}, 10000);
