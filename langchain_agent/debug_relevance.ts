import { db } from './src/config/database';
import { executeHybridSearch } from './src/tools/vector_search.tool';
import { embeddingsService } from './src/services/embeddings.service';
import dotenv from 'dotenv';

dotenv.config();

async function debug() {
    const query = "mens tshirt";
    console.log(`Debugging relevance for: "${query}"`);

    try {
        const embedding = await embeddingsService.generateEmbedding(query);
        const results = await executeHybridSearch({
            query,
            embedding,
            limit: 10,
            categories: ["men"] // Simulate what the agent would pick
        });

        console.log("\nTop Results:");
        results.forEach((r: any, i: number) => {
            console.log(`${i + 1}. [ID: ${r.id}] ${r.name} (Score: ${r.rrf_score})`);
        });

        // Let's inspect the search_context for the first 5
        console.log("\nInspecting Search Context for top 5:");
        for (const r of results.slice(0, 5)) {
            const detail = await db.query("SELECT search_context FROM products WHERE id = $1", [r.id]);
            console.log(`--- ID: ${r.id} (${r.name}) ---`);
            console.log(detail.rows[0]?.search_context);
        }

        process.exit(0);
    } catch (error) {
        console.error("Debug failed:", error);
        process.exit(1);
    }
}

debug();
