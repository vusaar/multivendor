import { db } from './src/config/database';

async function check() {
    try {
        const tables = [
            'categories',
            'product_variations',
            'variation_attributes',
            'variation_attribute_values'
        ];

        for (const table of tables) {
            console.log(`\nChecking ${table} table schema...`);
            const schemaResult = await db.query(`
                SELECT column_name, data_type 
                FROM information_schema.columns 
                WHERE table_name = '${table}'
            `);
            console.log(`Columns in ${table}:`);
            schemaResult.rows.forEach(row => {
                console.log(`- ${row.column_name}: ${row.data_type}`);
            });
        }

        process.exit(0);
    } catch (error) {
        console.error('Error during check:', error);
        process.exit(1);
    }
}

check();
