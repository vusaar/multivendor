import { processUserQuery } from './src/services/search.agent';
import dotenv from 'dotenv';

dotenv.config();

async function verifyEntities() {
    const query = "i am looking for nike sneakers";
    console.log(`Testing Query: "${query}"`);
    try {
        const result = await processUserQuery(query);
        console.log("Result Type:", typeof result);
        console.log("Is Array:", Array.isArray(result));
        console.log("Result:", JSON.stringify(result, null, 2));
    } catch (error) {
        console.error("Verification Failed:", error);
    }
}

verifyEntities();
