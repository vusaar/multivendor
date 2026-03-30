import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const r = await db.query("SELECT id, name FROM products WHERE id = '11'");
    if (r.rows.length === 0) {
        console.log("Product 11 NOT FOUND.");
    } else {
        console.log("Product 11 found:", r.rows[0]);
    }
} 
run().catch(console.error).finally(() => process.exit(0));
