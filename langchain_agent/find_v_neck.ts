import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const r = await db.query("SELECT id, name, description FROM products WHERE name ILIKE '%v-neck%' OR description ILIKE '%v-neck%'");
    if (r.rows.length === 0) {
        console.log("NO PRODUCTS FOUND WITH 'V-NECK'");
    } else {
        console.log("Products with 'v-neck':", r.rows.map(row => ({ id: row.id, name: row.name })));
    }
} 
run().catch(console.error).finally(() => process.exit(0));
