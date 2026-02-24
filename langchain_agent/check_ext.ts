import { db } from './src/config/database';

async function checkExtensions() {
    try {
        console.log('--- Available Extensions Check ---');
        const res = await db.query('SELECT name, default_version, installed_version, comment FROM pg_available_extensions ORDER BY name;');
        console.log(JSON.stringify(res.rows, null, 2));
        process.exit(0);
    } catch (error) {
        console.error('Error checking extensions:', error);
        process.exit(1);
    }
}

checkExtensions();
