import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const res = await db.query("SELECT id, name, search_context FROM products WHERE search_context ILIKE '%golf%' OR name ILIKE '%golf%'");
    if (res.rows.length === 0) {
        console.log("No products found with 'golf' in name or context.");
    } else {
        res.rows.forEach(r => {
            console.log(`\nID: ${r.id}, Name: ${r.name}`);
            console.log(`Search Context: ${r.search_context}`);
        });
    }
} 
run().catch(console.error).finally(() => process.exit(0));
