import { db } from './src/config/database';

async function killSlowQueries() {
    console.log("Terminating slow similarity queries...");
    try {
        const res = await db.query(`
            SELECT pid, query_start, query 
            FROM pg_stat_activity 
            WHERE state != 'idle' 
              AND (now() - query_start) > interval '1 minute'
              AND query NOT LIKE '%pg_stat_activity%'
        `);

        for (const row of res.rows) {
            console.log(`Killing PID ${row.pid} (Started: ${row.query_start})`);
            await db.query(`SELECT pg_terminate_backend(${row.pid})`);
        }
        console.log(`Successfully killed ${res.rows.length} queries.`);
    } catch (error: any) {
        console.error("Kill Error:", error.message);
    }
}

killSlowQueries().then(() => process.exit(0));
