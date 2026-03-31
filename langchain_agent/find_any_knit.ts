import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const r = await db.query("SELECT id, name, description, search_context FROM products WHERE name ILIKE '%knit%' OR description ILIKE '%knit%' OR search_context ILIKE '%knit%'");
    if (r.rows.length === 0) {
        console.log("NO PRODUCTS FOUND WITH 'KNIT'");
    } else {
        console.log("Products with 'knit':", r.rows.map(row => ({ id: row.id, name: row.name })));
    }
} 
run().catch(console.error).finally(() => process.exit(0));
