import { db } from './src/config/database';

async function testIndexOperator() {
    try {
        const query = "ladies tops";
        console.log("Testing % operator speed...");
        const start = Date.now();
        const res = await db.query(`
            EXPLAIN ANALYZE 
            SELECT id FROM products 
            WHERE status = 'active' AND search_context % '${query}'
            ORDER BY similarity(search_context, '${query}') DESC
            LIMIT 10
        `);
        console.log(res.rows.map(r => r['QUERY PLAN']).join('\n'));
        console.log(`Total time: ${Date.now() - start}ms`);
    } catch (e: any) {
        console.error("Error:", e.message);
    }
}

testIndexOperator().then(() => process.exit(0));
