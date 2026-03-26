import { db } from "./src/config/database";
import * as fs from 'fs';

async function run() {
    const res = await db.query(`SELECT id, name, description, search_context FROM products WHERE name ILIKE '%golf%' OR description ILIKE '%golf%' OR search_context ILIKE '%golf%';`);
    fs.writeFileSync('golf_db.json', JSON.stringify(res.rows, null, 2));
    process.exit(0);
}

run();
