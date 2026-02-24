import { processUserQuery } from './src/services/search.agent';

async function verifyPriceFix() {
    const query = "ladies tops for less than $10";
    console.log(`Verifying Query: "${query}"`);
    try {
        const results = await processUserQuery(query);
        console.log("Results:", JSON.stringify(results, null, 2));
    } catch (error: any) {
        console.error("Verification Error:", error.message);
    }
}

// Wait to avoid rate limits
console.log("Waiting 10s...");
setTimeout(() => {
    verifyPriceFix().then(() => process.exit(0));
}, 10000);
