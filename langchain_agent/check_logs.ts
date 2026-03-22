import { db } from './src/config/database';

async function checkRecentLogs() {
    console.log("\n📊 RECENT SEARCH LOGS");
    console.log("--------------------------------------------------");
    
    try {
        const res = await db.query(
            "SELECT * FROM search_logs ORDER BY created_at DESC LIMIT 1;"
        );

        if (res.rows.length > 0) {
            const log = res.rows[0];
            console.log(`Query: "${log.query}"`);
            console.log(`User: ${log.phone_number}`);
            console.log(`Intent: ${JSON.stringify(log.intent, null, 2)}`);
            console.log(`Results Count: ${log.results_count}`);
            console.log(`Top Result: ${JSON.stringify(log.results?.[0], null, 2)}`);
            console.log(`Duration: ${log.duration_ms}ms`);
        } else {
            console.log("No logs found.");
        }
    } catch (error: any) {
        console.error("Error querying logs:", error.message);
    }
    process.exit(0);
}

checkRecentLogs();
