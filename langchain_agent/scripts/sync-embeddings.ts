import { db } from '../src/config/database';
import { embeddingsService } from '../src/services/embeddings.service';
import dotenv from 'dotenv';

dotenv.config();

async function syncEmbeddings() {
    try {
        console.log('--- Starting Embedding Sync ---');

        // 1. Sync Categories
        console.log('\nSyncing Categories...');
        const categories = await db.query('SELECT id, name, description FROM categories');
        console.log(`Found ${categories.rows.length} categories.`);

        for (const cat of categories.rows) {
            const text = `Category: ${cat.name} | Description: ${cat.description || ''}`;
            console.log(`Generating embedding for category: ${cat.name}`);
            const embedding = await embeddingsService.generateEmbedding(text);

            await db.query(
                'UPDATE categories SET embedding = $1 WHERE id = $2',
                [`[${embedding.join(',')}]`, cat.id]
            );
        }

        // 2. Sync Products
        console.log('\nSyncing Products...');
        // Join with categories and brands for better context
        const products = await db.query(`
            SELECT p.id, p.name, p.description, 
                   c.name as category_name, 
                   pc.name as parent_category_name,
                   gc.name as grandparent_category_name,
                   b.name as brand_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN categories pc ON c.parent_id = pc.id
            LEFT JOIN categories gc ON pc.parent_id = gc.id
            LEFT JOIN brands b ON p.brand_id = b.id
        `);
        console.log(`Found ${products.rows.length} products.`);

        for (const prod of products.rows) {
            // Get variation attributes for even better context
            const variations = await db.query(`
                SELECT DISTINCT av.value
                FROM product_variations pv
                JOIN product_variation_attribute_value pvav ON pv.id = pvav.product_variation_id
                JOIN variation_attribute_values av ON pvav.variation_attribute_value_id = av.id
                WHERE pv.product_id = $1
            `, [prod.id]);

            prod.variations = variations.rows;
            const text = embeddingsService.formatProductForEmbedding(prod);

            console.log(`Generating embedding for product: ${prod.name}`);
            const embedding = await embeddingsService.generateEmbedding(text);

            await db.query(
                'UPDATE products SET embedding = $1, search_context = $2 WHERE id = $3',
                [`[${embedding.join(',')}]`, text, prod.id]
            );
        }

        console.log('\n--- Embedding Sync Complete ---');
        process.exit(0);
    } catch (error) {
        console.error('Error during sync:', error);
        process.exit(1);
    }
}

syncEmbeddings();
