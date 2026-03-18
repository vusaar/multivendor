import { db } from './src/config/database';
import dotenv from 'dotenv';

dotenv.config();

async function runQuery(sql: string) {
    try {
        const res = await db.query(sql);
        console.log(JSON.stringify(res.rows, null, 2));
        process.exit(0);
    } catch (error) {
        console.error('Query failed:', error);
        process.exit(1);
    }
}

runQuery(process.argv[2]);
