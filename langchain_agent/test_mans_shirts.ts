import { processUserQuery } from './src/services/search.agent';
import dotenv from 'dotenv';

dotenv.config();

async function runTest() {
    const query = "mans shirts";
    console.log(`Test Query: ${query}`);
    try {
        const response = await processUserQuery(query);
        console.log("Agent Final Response:", JSON.stringify(response, null, 2));
    } catch (error) {
        console.error("Test Failed:", error);
    }
}

runTest();
