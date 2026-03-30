import { db } from './src/config/database';
import dotenv from 'dotenv';
dotenv.config();

async function check() {
    const query = 'shoe';
    const sql = `
        SELECT name, word_similarity($1, name) as sim 
        FROM products 
        WHERE word_similarity($1, name) > 0.4 
        ORDER BY sim DESC 
        LIMIT 20
    `;
    
    const res = await db.query(sql, [query]);
    console.table(res.rows);
}

check().catch(console.error).finally(() => process.exit(0));
