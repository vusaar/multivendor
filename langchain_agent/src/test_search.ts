import { processUserQuery } from './services/search.agent';
import dotenv from 'dotenv';

dotenv.config();

async function runTest() {
    const query = "Show me active products in the Electronics category";
    console.log(`Test Query: ${query}`);
    try {
        const response = await processUserQuery(query);
        console.log("Response:", response);
    } catch (error) {
        console.error("Test Failed:", error);
    }
}

runTest();
