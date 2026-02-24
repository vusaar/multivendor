import { processUserQuery } from '../src/services/search.agent';
import dotenv from 'dotenv';

dotenv.config();

async function runTests() {
    const queries = [
        "something warm to wear",
        "electronics under $1000",
        "blue accessories",
        "mobile devices"
    ];

    console.log("--- Starting Hybrid Search Verification ---\n");

    for (const query of queries) {
        console.log(`Query: "${query}"`);
        try {
            const results = await processUserQuery(query);
            console.log("Results:");
            if (Array.isArray(results)) {
                results.forEach((r, i) => {
                    console.log(`${i + 1}. ${r.name} (Score: ${r.rrf_score || r.relevance_score || 'N/A'}, Price: ${r.price})`);
                });
            } else {
                console.log(JSON.stringify(results, null, 2));
            }
            console.log("-------------------\n");
        } catch (error) {
            console.error(`Error processing query "${query}":`, error);
        }
    }
}

runTests();
