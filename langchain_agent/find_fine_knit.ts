import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const r = await db.query("SELECT id, name, description, search_context FROM products WHERE search_context ILIKE '%fine-knit%' OR description ILIKE '%fine-knit%' OR name ILIKE '%fine-knit%'");
    if (r.rows.length === 0) {
        console.log("NO PRODUCTS FOUND WITH 'FINE-KNIT'");
    } else {
        console.log("Products with 'fine-knit':", r.rows.map(row => ({ id: row.id, name: row.name })));
    }
} 
run().catch(console.error).finally(() => process.exit(0));
