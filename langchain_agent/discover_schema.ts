import { db } from './src/config/database';
import fs from 'fs';
import dotenv from 'dotenv';
dotenv.config();

async function discoverSchema() {
    try {
        const schema: any = {};
        const tablesRes = await db.query(`SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'`);
        const tables = tablesRes.rows.map((r: any) => r.table_name);

        for (const table of tables) {
            const columnsRes = await db.query(`SELECT column_name, data_type FROM information_schema.columns WHERE table_name = $1`, [table]);
            schema[table] = columnsRes.rows.map((c: any) => ({ name: c.column_name, type: c.data_type }));
        }

        fs.writeFileSync('schema.json', JSON.stringify(schema, null, 2));
        console.log("Schema written to schema.json");
        process.exit(0);
    } catch (error) {
        process.exit(1);
    }
}
discoverSchema();
