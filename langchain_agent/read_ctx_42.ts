import { db } from './src/config/database'; 
require('dotenv').config(); 
async function run() { 
    const res = await db.query("SELECT id, name, search_context FROM products WHERE id = '42'");
    console.log(res.rows[0].search_context);
} 
run().catch(console.error).finally(() => process.exit(0));
