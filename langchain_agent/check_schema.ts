import { db } from "./src/config/database";

async function run() {
    try {
        const res = await db.query(`
            SELECT column_name, data_type 
            FROM information_schema.columns 
            WHERE table_name = 'search_logs'
            ORDER BY ordinal_position;
        `);
        console.log(JSON.stringify(res.rows, null, 2));
    } catch (e) {
        console.log(e);
    }
    process.exit(0);
}

run();
