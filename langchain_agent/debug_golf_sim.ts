import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const qDesc = await db.query("SELECT word_similarity('golf tshirts', 'Golf t-shirt') as sim");
    const aDesc = await db.query("SELECT word_similarity('golf', 'Golf t-shirt') as sim");
    const aDescStrict = await db.query("SELECT strict_word_similarity('golf', 'Golf t-shirt') as sim");

    console.log("Query 'golf tshirts' vs 'Golf t-shirt' (Normal):", qDesc.rows[0].sim);
    console.log("Attribute 'golf' vs 'Golf t-shirt' (Normal):", aDesc.rows[0].sim);
    console.log("Attribute 'golf' vs 'Golf t-shirt' (Strict):", aDescStrict.rows[0].sim);
} 
run().catch(console.error).finally(() => process.exit(0));
