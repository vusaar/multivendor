import { db } from './src/config/database';

async function checkDetails() {
    try {
        const version = await db.query('SELECT version();');
        console.log('OS/Postgres Version:', version.rows[0].version);

        const ext = await db.query('SELECT name, default_version FROM pg_available_extensions WHERE name = \'vector\'');
        console.log('Vector available:', ext.rows.length > 0 ? 'Yes' : 'No');

        process.exit(0);
    } catch (e) {
        console.error(e);
        process.exit(1);
    }
}

checkDetails();
