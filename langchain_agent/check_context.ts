import { db } from './src/config/database';
import dotenv from 'dotenv';
dotenv.config();

async function checkContext() {
    try {
        const res = await db.query("SELECT id, name, status, search_context FROM products WHERE search_context ILIKE '%black%';");
        let output = "";
        res.rows.forEach(r => {
            output += `[${r.id}] ${r.name} - Status: ${r.status}\nContext: ${r.search_context}\n\n`;
        });
        require('fs').writeFileSync('black_products.txt', output);
        console.log("Wrote to black_products.txt");
        process.exit(0);
    } catch (e) {
        console.error(e);
        process.exit(1);
    }
}
checkContext();
