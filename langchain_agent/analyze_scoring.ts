import { db } from './src/config/database';
import dotenv from 'dotenv';
import * as fs from 'fs';

dotenv.config();

async function analyzeScore(query: string, entity: string, synonyms: string[], productId: number) {
    let log = `\n--- Analyzing Score for Query: "${query}", Entity: "${entity}", Product ID: ${productId} ---\n`;
    
    // Construct the precisionScoreSql part manually for inspection
    const sqlParams: any[] = [];
    const idIdx = sqlParams.push(productId); // $1
    const qIdx = sqlParams.push(query.toLowerCase().trim()); // $2
    const pIdx = sqlParams.push(entity.toLowerCase().trim()); // $3
    
    let precisionScoreSql = `(
        (CASE WHEN LOWER(name) = $2 THEN 150.0 ELSE 0.0 END) +
        (CASE WHEN REPLACE(LOWER(name), '-', '') = REPLACE($2, '-', '') THEN 150.0 ELSE 0.0 END) +
        (word_similarity(LOWER(name), $2) * 60.0)
    )`;

    precisionScoreSql += ` + (strict_word_similarity(LOWER(name), $3) * 150.0)`;
    precisionScoreSql += ` + (word_similarity(LOWER(search_context), $3) * 60.0)`;
    precisionScoreSql += ` + (CASE WHEN LOWER(name) = $3 THEN 40.0 ELSE 0.0 END)`;

    synonyms.forEach((syn, i) => {
        sqlParams.push(syn.toLowerCase().trim());
        const sIdx = sqlParams.length;
        precisionScoreSql += ` + (word_similarity(LOWER(name), $${sIdx}) * 80.0)`;
        precisionScoreSql += ` + (word_similarity(LOWER(search_context), $${sIdx}) * 30.0)`;
    });

    const sql = `
        SELECT 
            name,
            (CASE WHEN LOWER(name) = $2 THEN 150.0 ELSE 0.0 END) as literal_match,
            (word_similarity(LOWER(name), $2) * 60.0) as query_fuzzy,
            (strict_word_similarity(LOWER(name), $3) * 150.0) as entity_strict,
            (word_similarity(LOWER(search_context), $3) * 60.0) as entity_context,
            (${precisionScoreSql}) as total_precision
        FROM products
        WHERE id = $1;
    `;

    try {
        const res = await db.query(sql, sqlParams);
        console.table(res.rows);
        log += JSON.stringify(res.rows, null, 2) + '\n';
        fs.appendFileSync('analyze_output.txt', log);
    } catch (err: any) {
        const errLog = `ERROR in analyzeScore: ${err.message}\n`;
        console.error(errLog);
        fs.appendFileSync('analyze_output.txt', errLog);
    }
}

async function run() {
    if (fs.existsSync('analyze_output.txt')) fs.unlinkSync('analyze_output.txt');
    await db.query("SELECT set_config('pg_trgm.word_similarity_threshold', '0.5', true), set_config('pg_trgm.strict_word_similarity_threshold', '0.5', true)");
    
    // case "shoes under 50" -> entity "shoe", syns "sneaker", "kick", "footwear"
    await analyzeScore("shoes under 50", "shoe", ["sneaker", "kick", "footwear"], 55); // Shirt ID 55
    await analyzeScore("shoes under 50", "shoe", ["sneaker", "kick", "footwear"], 59); // Sneaker ID 59
    
    // case "Pizza"
    await analyzeScore("Pizza", "Pizza", [], 5); // Red Nail Polish
    
    process.exit(0);
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
