import { db } from './src/config/database';

async function checkProducts() {
    try {
        const res = await db.query(
            "SELECT id, name FROM products LIMIT 50"
        );
        console.log("Found products:");
        console.log(JSON.stringify(res.rows, null, 2));
    } catch (error) {
        console.error("Error checking products:", error);
    } finally {
        process.exit(0);
    }
}

checkProducts();
