import { db } from './src/config/database';
import dotenv from 'dotenv';
dotenv.config();

async function getCategories() {
    try {
        const res = await db.query(`
            SELECT DISTINCT
               substring(search_context from 'CategoryPath: ([^|]*)') as cat,
               COUNT(id) as cnt
            FROM products
            GROUP BY 1
            ORDER BY cnt DESC
            LIMIT 10;
        `);
        console.log("Categories:\n", res.rows);
        process.exit(0);
    } catch(e) { console.error(e); process.exit(1); }
}
getCategories();
