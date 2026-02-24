import { processUserQuery } from './src/services/search.agent';

async function deepDebug() {
    const query = "ladies tops for less than $10";
    console.log(`Deep Debug Query: "${query}"`);
    try {
        const results = await processUserQuery(query);
        console.log("Top 20 Results:");
        if (Array.isArray(results)) {
            results.forEach((r, i) => {
                console.log(`${i + 1}. [ID: ${r.id}] Name: ${r.name}, Price: ${r.price}, Description: ${r.description}`);
            });
        } else {
            console.log("Response:", JSON.stringify(results, null, 2));
        }
    } catch (error: any) {
        console.error("Deep Debug Error:", error.message);
    }
}

deepDebug().then(() => process.exit(0));
