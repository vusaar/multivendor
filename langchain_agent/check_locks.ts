import { db } from './src/config/database';

async function checkLocks() {
    try {
        const res = await db.query(`
            SELECT pid, now() - query_start as duration, query, state 
            FROM pg_stat_activity 
            WHERE state != 'idle' 
              AND query NOT LIKE '%pg_stat_activity%'
        `);
        console.log("Active Queries:");
        console.log(JSON.stringify(res.rows, null, 2));
    } catch (e: any) {
        console.error("Error:", e.message);
    }
}

checkLocks().then(() => process.exit(0));
