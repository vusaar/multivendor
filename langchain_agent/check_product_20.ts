import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const r = await db.query("SELECT id, name, description, search_context FROM products WHERE id = '20'");
    console.log(r.rows[0]);
} 
run().catch(console.error).finally(() => process.exit(0));
