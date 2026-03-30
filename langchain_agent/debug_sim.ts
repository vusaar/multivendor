import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const skirtShirt = await db.query("SELECT word_similarity('skirt', 'Shirt') as sim");
    const shoeShirt = await db.query("SELECT word_similarity('shoe', 'shirt') as sim");
    const shoesSneaker = await db.query("SELECT word_similarity('shoes', 'Sneaker') as sim");
    const shoesSneakers = await db.query("SELECT word_similarity('shoes', 'sneakers') as sim");

    console.log("shoes vs Sneaker:", shoesSneaker.rows[0].sim);
    console.log("shoes vs sneakers:", shoesSneakers.rows[0].sim);
} 
run().catch(console.error).finally(() => process.exit(0));
