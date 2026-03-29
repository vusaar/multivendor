import { db } from './src/config/database';
import dotenv from 'dotenv';
dotenv.config();

async function testSimilarity(query: string, productName: string) {
    const sql = `
        SELECT 
            similarity($1, $2) as sim,
            word_similarity($1, $2) as word_sim,
            strict_word_similarity($1, $2) as strict_sim
    `;
    const res = await db.query(sql, [query, productName]);
    console.log(`\nQuery: "${query}" vs Name: "${productName}"`);
    console.log(`Similarity: ${res.rows[0].sim}`);
    console.log(`Word Similarity: ${res.rows[0].word_sim}`);
    console.log(`Strict Word Similarity: ${res.rows[0].strict_sim}`);
}

async function run() {
    await testSimilarity("shoes under 50", "Shirt");
    await testSimilarity("shoes", "Adidas AZX750 Sneaker");
    await testSimilarity("sneaker", "Adidas AZX750 Sneaker");
    await testSimilarity("eyeshadow", "Eyeshadow Palette with Mirror");
    await testSimilarity("tshirt", "Round Neck Blue T-shirt");
    await testSimilarity("shirt", "Dior White T-shirt");
    process.exit(0);
}
run();
