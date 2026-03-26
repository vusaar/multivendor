import { db } from "./src/config/database";
import * as fs from 'fs';

async function run() {
    try {
        const res = await db.query(`SELECT id, name, search_context FROM products WHERE name ILIKE '%adidas%';`);
        fs.writeFileSync('adidas_sneaker.json', JSON.stringify(res.rows, null, 2));
    } catch (e) {
        console.log(e);
    }
    process.exit(0);
}

run();
