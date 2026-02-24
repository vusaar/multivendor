import { db } from './src/config/database';

async function analyzePerformance() {
    console.log("Analyzing search performance...");
    try {
        const query = "ladies tops";

        console.log("\n--- EXPLAIN ANALYZE for similarity search ---");
        const res = await db.query(`
            EXPLAIN ANALYZE 
            SELECT id, name 
            FROM products 
            WHERE status = 'active' 
              AND similarity(search_context, '${query}') > 0.05 
            ORDER BY similarity(search_context, '${query}') DESC 
            LIMIT 5
        `);
        console.log(res.rows.map(r => r['QUERY PLAN']).join('\n'));

        console.log("\n--- Index Stats ---");
        const stats = await db.query(`
            SELECT indexname, indexdef 
            FROM pg_indexes 
            WHERE tablename = 'products' 
              AND indexname LIKE '%trgm%'
        `);
        console.log(JSON.stringify(stats.rows, null, 2));

    } catch (error: any) {
        console.error("Analysis Error:", error.message);
    }
}

analyzePerformance().then(() => process.exit(0));
