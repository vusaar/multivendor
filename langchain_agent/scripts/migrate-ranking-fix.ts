import { db } from '../src/config/database';
import dotenv from 'dotenv';

dotenv.config();

async function runMigration() {
    try {
        console.log('Adding search_context column and creating gist index...');

        // 1. Add column
        await db.query('ALTER TABLE products ADD COLUMN IF NOT EXISTS search_context TEXT');
        console.log('Column added (if not existed).');

        // 2. Create index
        await db.query('CREATE INDEX IF NOT EXISTS products_search_context_idx ON products USING gist (search_context gist_trgm_ops)');
        console.log('Index created (if not existed).');

        console.log('Migration successful.');
        process.exit(0);
    } catch (error) {
        console.error('Migration failed:', error);
        process.exit(1);
    }
}

runMigration();
