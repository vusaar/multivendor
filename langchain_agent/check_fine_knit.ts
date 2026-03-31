import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const r1 = await db.query("SELECT word_similarity('fine knit', 'Fine-knit Blouse') as sim");
    const r2 = await db.query("SELECT word_similarity('fine knit', 'Fine knit Blouse') as sim");
    const r3 = await db.query("SELECT word_similarity('fine', 'Fine-knit Blouse') as sim");
    const r4 = await db.query("SELECT word_similarity('knit', 'Fine-knit Blouse') as sim");

    console.log("Fuzzy (fine knit -> Fine-knit):", r1.rows[0].sim);
    console.log("Fuzzy (fine knit -> Fine knit):", r2.rows[0].sim);
    console.log("Fuzzy (fine -> Fine-knit):", r3.rows[0].sim);
    console.log("Fuzzy (knit -> Fine-knit):", r4.rows[0].sim);
} 
run().catch(console.error).finally(() => process.exit(0));
