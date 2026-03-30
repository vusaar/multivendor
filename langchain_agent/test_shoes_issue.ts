import { processUserQuery } from './src/services/search.agent';
import { executeHybridSearch } from './src/tools/vector_search.tool';
import { embeddingsService } from './src/services/embeddings.service';
import dotenv from 'dotenv';

dotenv.config();

async function debugShoes() {
    const query = "shoes";
    console.log(`--- DEBUGGING QUERY: "${query}" ---`);

    // 1. Check Agent Intent Extraction
    const results = await processUserQuery(query, "debug_user_" + Date.now());
    
    console.log("\n--- AGENT RESULTS ---");
    results.slice(0, 5).forEach((r: any, i: number) => {
        console.log(`${i+1}. ${r.name} (Score: ${r.rrf_score || r.score})`);
    });

    // 2. Deep Dive into SQL Scoring
    const embedding = await embeddingsService.generateEmbedding(query);
    const sqlResults = await executeHybridSearch({
        query: query,
        entity: "shoe",
        synonyms: ["sneaker", "kick", "footwear"],
        embedding,
        limit: 10
    });

    console.log("\n--- DEEP SQL RESULTS (Raw) ---");
    sqlResults.forEach((r: any, i: number) => {
        console.log(`${i+1}. ${r.name} (RRF: ${r.rrf_score}, V: ${r.v_score}, P: ${r.p_score})`);
    });
}

debugShoes().catch(console.error);
