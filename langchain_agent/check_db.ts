import { db } from './src/config/database';

async function check() {
    try {
        console.log('Checking for pgvector extension...');
        const extResult = await db.query("SELECT * FROM pg_extension WHERE extname = 'vector'");
        if (extResult.rows.length > 0) {
            console.log('pgvector extension is INSTALLED.');
        } else {
            console.log('pgvector extension is NOT installed.');
        }

        console.log('\nChecking products table schema...');
        const schemaResult = await db.query(`
            SELECT column_name, data_type 
            FROM information_schema.columns 
            WHERE table_name = 'products'
        `);
        console.log('Columns in products table:');
        schemaResult.rows.forEach(row => {
            console.log(`- ${row.column_name}: ${row.data_type}`);
        });

        process.exit(0);
    } catch (error) {
        console.error('Error during check:', error);
        process.exit(1);
    }
}

check();
