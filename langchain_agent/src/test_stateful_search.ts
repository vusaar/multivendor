import { processUserQuery } from './services/search.agent';
import dotenv from 'dotenv';

dotenv.config();

async function runVerification() {
    const userId = "test_user_" + Date.now();

    console.log("--- STEP 1: Initial Search (Should Reasoning + Embedding) ---");
    const start1 = Date.now();
    const res1 = await processUserQuery("blue shirts", userId);
    console.log(`Step 1 took: ${Date.now() - start1}ms`);
    console.log(`Results found: ${Array.isArray(res1) ? res1.length : 0}`);

    console.log("\n--- STEP 2: Continuation Search (Should BYPASS Reasoning + Embedding) ---");
    const start2 = Date.now();
    const res2 = await processUserQuery("more", userId);
    console.log(`Step 2 took: ${Date.now() - start2}ms`);
    console.log(`Results found: ${Array.isArray(res2) ? res2.length : 0}`);

    if (Date.now() - start2 < (Date.now() - start1) / 2) {
        console.log("\n✅ SUCCESS: Step 2 was significantly faster (likely bypassed LLM)!");
    } else {
        console.log("\n⚠️ WARNING: Step 2 was not significantly faster. Check logs for bypass.");
    }
}

runVerification().catch(console.error);
