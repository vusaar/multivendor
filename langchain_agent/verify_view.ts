import { db } from './src/config/database';

async function verifyViewSearch() {
    console.log("Verifying search using Materialized View join...");
    try {
        const query = "ladies tops";

        console.log("\n--- EXPLAIN ANALYZE for new view-based join ---");
        const res = await db.query(`
            EXPLAIN ANALYZE 
            SELECT p.id, p.name 
            FROM products p
            LEFT JOIN product_variation_metadata pvm ON p.id = pvm.product_id
            WHERE p.status = 'active'
              AND (
                similarity(p.name, '${query}') > 0.05
                OR similarity(pvm.variation_search_text, '${query}') > 0.05
              )
            ORDER BY similarity(p.name, '${query}') DESC
            LIMIT 5
        `);
        console.log(res.rows.map(r => r['QUERY PLAN']).join('\n'));

    } catch (error: any) {
        console.error("Verification Error:", error.message);
    }
}

verifyViewSearch().then(() => process.exit(0));
