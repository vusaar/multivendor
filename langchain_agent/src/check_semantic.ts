import { db } from './config/database';
import { embeddingsService } from './services/embeddings.service';
import dotenv from 'dotenv';
dotenv.config();

import * as fs from 'fs';

async function checkSemantic() {
    const query = "white tshirt";
    let output = `Query: ${query}\n`;

    const queryEmbedding = await embeddingsService.generateEmbedding(query);
    const embeddingStr = `[${queryEmbedding.join(",")}]`;

    const sql = `
        SELECT id, name, 
               (embedding <=> $1::vector) as distance
        FROM products
        WHERE id IN (2, 8, 12, 50)
        ORDER BY distance ASC;
    `;

    const res = await db.query(sql, [embeddingStr]);
    output += "\nSemantic Distances (Lower is better):\n";
    res.rows.forEach(row => {
        output += `ID: ${row.id} | Name: ${row.name} | Distance: ${row.distance}\n`;
    });

    fs.writeFileSync('semantic_results.txt', output);
    console.log("Results written to semantic_results.txt");
}

checkSemantic();
