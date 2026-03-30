import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const r = await db.query("SELECT word_similarity('shoes', 'shirt') as sim");
    console.log("Similarity 'shoes' vs 'shirt':", r.rows[0].sim);
} 
run().catch(console.error).finally(() => process.exit(0));
