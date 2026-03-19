import { db } from './src/config/database';
import { executeHybridSearch } from './src/tools/vector_search.tool';
import { embeddingsService } from './src/services/embeddings.service';
import dotenv from 'dotenv';

dotenv.config();

async function debug() {
    const query = "gents tshirt";
    console.log(`Debugging relevance for: "${query}"`);

    try {
        const embedding = await embeddingsService.generateEmbedding(query);
        const results = await executeHybridSearch({
            query,
            embedding,
            limit: 10,
            categories: ["men", "gents"] // What the agent extracts for "gents tshirt"
        });

        console.log("\nTop Results:");
        results.forEach((r: any, i: number) => {
            console.log(`${i + 1}. [ID: ${r.id}] ${r.name} (Score: ${r.rrf_score})`);
        });

        console.log("\nInspecting Search Context for top 10:");
        for (const r of results) {
            const detail = await db.query("SELECT search_context FROM products WHERE id = $1", [r.id]);
            console.log(`--- ID: ${r.id} (${r.name}) [Score: ${r.rrf_score}] ---`);
            console.log(detail.rows[0]?.search_context);
        }

        process.exit(0);
    } catch (error) {
        console.error("Debug failed:", error);
        process.exit(1);
    }
}

debug();
