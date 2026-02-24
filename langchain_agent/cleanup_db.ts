import { db } from './src/config/database';

async function cleanupDB() {
    console.log("Checking for hung queries...");
    try {
        // Find PID of queries running longer than 15 minutes
        const activeQueries = await db.query(`
            SELECT pid, now() - query_start as duration, query 
            FROM pg_stat_activity 
            WHERE state != 'idle' 
              AND (now() - query_start) > interval '15 minutes'
              AND query NOT LIKE '%pg_stat_activity%'
        `);

        if (activeQueries.rows.length === 0) {
            console.log("No hung queries found.");
        } else {
            console.log(`Found ${activeQueries.rows.length} hung queries.`);
            for (const row of activeQueries.rows) {
                console.log(`Killing PID: ${row.pid} (Duration: ${JSON.stringify(row.duration)})`);
                console.log(`Query: ${row.query.substring(0, 500)}`);
                await db.query(`SELECT pg_terminate_backend(${row.pid})`);
            }
        }
    } catch (error: any) {
        console.error("Cleanup Error:", error.message);
    }
}

cleanupDB().then(() => process.exit(0));
