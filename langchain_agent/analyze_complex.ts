import { db } from './src/config/database';

async function analyzeComplexQuery() {
    console.log("Analyzing complex search join...");
    try {
        const query = "ladies tops";
        const res = await db.query(`
            EXPLAIN ANALYZE 
            SELECT p.id 
            FROM products p
            INNER JOIN vendors v ON p.vendor_id = v.id
            INNER JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_variation_metadata pvm ON p.id = pvm.product_id
            WHERE p.status = 'active'
              AND (
                similarity(p.name, '${query}') > 0.05
                OR similarity(p.description, '${query}') > 0.05
                OR similarity(pvm.variation_search_text, '${query}') > 0.05
              )
        `);
        console.log(res.rows.map(r => r['QUERY PLAN']).join('\n'));
    } catch (error: any) {
        console.error("Analysis Error:", error.message);
    }
}

analyzeComplexQuery().then(() => process.exit(0));
