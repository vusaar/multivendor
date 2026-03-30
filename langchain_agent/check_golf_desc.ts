import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const res = await db.query("SELECT id, name, description FROM products WHERE description ILIKE '%golf%'");
    if (res.rows.length === 0) {
        console.log("No products found with 'golf' in description.");
    } else {
        res.rows.forEach(r => {
            console.log(`\nID: ${r.id}, Name: ${r.name}`);
            console.log(`Description: ${r.description}`);
        });
    }
} 
run().catch(console.error).finally(() => process.exit(0));
