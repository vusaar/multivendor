import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const res = await db.query("SELECT id, name, search_context FROM products WHERE id IN ('63', '32', '41', '60')");
    res.rows.forEach(r => {
        console.log(`\nID: ${r.id}, Name: ${r.name}`);
        console.log(`Search Context: ${r.search_context}`);
    });
} 
run().catch(console.error).finally(() => process.exit(0));
