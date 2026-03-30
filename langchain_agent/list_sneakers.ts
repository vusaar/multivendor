import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const r = await db.query("SELECT id, name FROM products WHERE name ILIKE '%Sneaker%'");
    console.log(r.rows);
} 
run().catch(console.error).finally(() => process.exit(0));
