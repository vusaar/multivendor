import { db } from '../src/config/database';
import dotenv from 'dotenv';

dotenv.config();

async function checkSchema() {
    try {
        const res = await db.query(`
            SELECT column_name, data_type 
            FROM information_schema.columns 
            WHERE table_name = 'products'
        `);
        console.log('Columns in products table:');
        res.rows.forEach(row => console.log(`- ${row.column_name}: ${row.data_type}`));
        process.exit(0);
    } catch (error) {
        console.error('Error checking schema:', error);
        process.exit(1);
    }
}

checkSchema();
