import { db } from './src/config/database';
require('dotenv').config();

async function run() {
    console.log("Checking Product 59 Heritage...");
    const p = await db.oneOrNone('SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = 59');
    console.log('PRODUCT 59:', JSON.stringify(p, null, 2));

    if (p && p.category_id) {
        console.log("Checking Lineage CTE for Category:", p.category_id);
        const lineage = await db.oneOrNone(`
            WITH RECURSIVE category_hierarchy AS (
                SELECT id, name, id as root_id, name as root_name, name::text as full_path
                FROM categories
                WHERE parent_id IS NULL
                UNION ALL
                SELECT c.id, c.name, ch.root_id, ch.root_name, (ch.full_path || ' > ' || c.name)
                FROM categories c
                JOIN category_hierarchy ch ON c.parent_id = ch.id
            )
            SELECT * FROM category_hierarchy WHERE id = $1
        `, [p.category_id]);
        console.log('LINEAGE RESULT:', JSON.stringify(lineage, null, 2));
    } else {
        console.log("CRITICAL: Product 59 has NO category_id!");
    }
}

run().catch(console.error).finally(() => process.exit(0));
