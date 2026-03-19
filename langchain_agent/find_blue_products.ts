import { db } from './src/config/database';
async function find() {
    const res = await db.query(`
        SELECT p.id, p.name, p.search_context
        FROM products p
        WHERE search_context ILIKE '%Blue%'
        LIMIT 10
    `);
    console.log(JSON.stringify(res.rows, null, 2));
}
find();
