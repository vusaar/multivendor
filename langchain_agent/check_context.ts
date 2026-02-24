import { db } from './src/config/database';

async function checkSearchContext() {
    try {
        const res = await db.query("SELECT id, name, search_context FROM products WHERE search_context IS NOT NULL LIMIT 5");
        console.log("Search Context Sample:");
        console.log(JSON.stringify(res.rows, null, 2));
    } catch (e: any) {
        console.error("Error:", e.message);
    }
}

checkSearchContext().then(() => process.exit(0));
