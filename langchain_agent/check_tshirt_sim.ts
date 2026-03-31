import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const tvs1 = await db.query("SELECT word_similarity('tshirt', 't-shirt') as sim"); 
    const tvs2 = await db.query("SELECT word_similarity('t-shirt', 'tshirt') as sim"); 
    const tvs3 = await db.query("SELECT strict_word_similarity('tshirt', 't-shirt') as sim");
    const tvs4 = await db.query("SELECT word_similarity('t-shirt', 'Prada T-shirt') as sim");

    console.log("tshirt vs t-shirt (Word):", tvs1.rows[0].sim);
    console.log("t-shirt vs tshirt (Word):", tvs2.rows[0].sim);
    console.log("tshirt vs t-shirt (Strict):", tvs3.rows[0].sim);
    console.log("t-shirt vs Prada T-shirt (Word):", tvs4.rows[0].sim);
} 
run().catch(console.error).finally(() => process.exit(0));
