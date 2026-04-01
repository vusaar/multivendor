import { db } from './src/config/database';
require('dotenv').config();

async function runDebug() {
    const sql = `
        WITH RECURSIVE category_hierarchy AS (
            SELECT id, name, id as root_id, name as root_name
            FROM categories
            WHERE parent_id IS NULL
            UNION ALL
            SELECT c.id, c.name, ch.root_id, ch.root_name
            FROM categories c
            JOIN category_hierarchy ch ON c.parent_id = ch.id
        )
        SELECT p.id, p.name, p.category_id, ch.root_name as demographic_root
        FROM products p
        LEFT JOIN category_hierarchy ch ON p.category_id = ch.id
        WHERE p.id = 17;
    `;
    
    try {
        const res = await db.query(sql);
        console.log("Path for Category 102 (Men T-shirts):");
        console.log(JSON.stringify(res.rows, null, 2));
        
        // Also trace path upwards from 117
        const res2 = await db.query(sql.replace('102', '117'));
        console.log("\nPath for Category 117 (Women Tops):");
        console.log(JSON.stringify(res2.rows, null, 2));
    } catch (e) {
        console.error(e);
    }
}

runDebug().finally(() => process.exit(0));
