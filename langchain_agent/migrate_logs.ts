import { db } from "./src/config/database";

async function run() {
    try {
        console.log("Adding columns to search_logs...");
        await db.query(`
            ALTER TABLE search_logs 
            ADD COLUMN IF NOT EXISTS corrected_query TEXT,
            ADD COLUMN IF NOT EXISTS search_id UUID DEFAULT gen_random_uuid();
        `);
        console.log("Migration successful.");
    } catch (e: any) {
        console.error("Migration failed:", e.message);
    }
    process.exit(0);
}

run();
