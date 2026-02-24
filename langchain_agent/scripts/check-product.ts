import { db } from '../src/config/database';
import dotenv from 'dotenv';

dotenv.config();

async function checkProduct(id: string) {
    try {
        const res = await db.query(`
            SELECT p.id, p.name, p.search_context
            FROM products p
            WHERE p.id = $1
        `, [id]);

        if (res.rows.length > 0) {
            console.log('Product Details:');
            console.log(JSON.stringify(res.rows[0], null, 2));
        } else {
            console.log('Product not found.');
        }
        process.exit(0);
    } catch (error) {
        console.error('Error checking product:', error);
        process.exit(1);
    }
}

checkProduct(process.argv[2]);
