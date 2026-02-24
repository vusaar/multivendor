import { db } from './src/config/database';

async function setup() {
    try {
        console.log('--- Database Setup Start ---');

        // 1. Enable extensions
        console.log('Enabling pgvector and pg_trgm extensions...');
        await db.query('CREATE EXTENSION IF NOT EXISTS vector;');
        await db.query('CREATE EXTENSION IF NOT EXISTS pg_trgm;');
        console.log('Extensions enabled successfully.');

        // 2. Add embedding columns
        console.log('\nAdding embedding columns to products and categories...');

        // Products
        const prodCols = await db.query("SELECT column_name FROM information_schema.columns WHERE table_name = 'products' AND column_name = 'embedding'");
        if (prodCols.rows.length === 0) {
            await db.query('ALTER TABLE products ADD COLUMN embedding vector(768);');
            console.log('Added column embedding to products.');
        } else {
            console.log('Column embedding already exists in products.');
        }

        // Categories
        const catCols = await db.query("SELECT column_name FROM information_schema.columns WHERE table_name = 'categories' AND column_name = 'embedding'");
        if (catCols.rows.length === 0) {
            await db.query('ALTER TABLE categories ADD COLUMN embedding vector(768);');
            console.log('Added column embedding to categories.');
        } else {
            console.log('Column embedding already exists in categories.');
        }

        // 3. Create Indexes
        console.log('\nCreating HNSW and GIST indexes...');
        // HNSW for vector search
        await db.query('CREATE INDEX IF NOT EXISTS products_embedding_hnsw_idx ON products USING hnsw (embedding vector_cosine_ops);');
        await db.query('CREATE INDEX IF NOT EXISTS categories_embedding_hnsw_idx ON categories USING hnsw (embedding vector_cosine_ops);');

        // GIST for trgm keyword search (name and description)
        await db.query('CREATE INDEX IF NOT EXISTS products_name_trgm_idx ON products USING gist (name gist_trgm_ops);');

        console.log('Indexes created successfully.');
        console.log('--- Database Setup Complete ---');
        process.exit(0);
    } catch (error: any) {
        console.error('Error during setup:', error);
        if (error.code === '42501') {
            console.error('\nCRITICAL: Insufficient privileges to install extensions. You may need to enable "pgvector" and "pg_trgm" manually in your database console.');
        }
        process.exit(1);
    }
}

setup();
