import { executeHybridSearch } from './src/tools/vector_search.tool';
import { embeddingsService } from './src/services/embeddings.service';
import { db } from './src/config/database';
import dotenv from 'dotenv';
dotenv.config();

async function debugRelevance() {
    const query = "blue tshirts";
    console.log(`Simulating search for: "${query}"`);

    try {
        const embedding = await embeddingsService.generateEmbedding(query);
        
        // Simulating what the AI SHOULD extract:
        const params = {
            query,
            embedding,
            limit: 5,
            entity: 'tshirt',
            attributes: ['blue']
        };

        // Actually, let's modify executeHybridSearch to return it or just query it manually
        const searchResults = await executeHybridSearch({...params, limit: 50});
        console.log("Search Results (Top 10):");
        searchResults.slice(0, 10).forEach((r, i) => {
            console.log(`${i+1}. [${r.id}] ${r.name} - Score: ${r.rrf_score}`);
        });

        // Find the specific blue tshirt (ID 15)
        const blueTshirt = searchResults.find(r => r.id === 15);
        if (blueTshirt) {
            console.log(`Blue Tshirt (ID 15) Rank: ${searchResults.indexOf(blueTshirt) + 1}, Score: ${blueTshirt.rrf_score}`);
        } else {
            console.log("Blue Tshirt (ID 15) NOT FOUND in top 50 results!");
        }

        process.exit(0);
    } catch (e) {
        console.error(e);
        process.exit(1);
    }
}
debugRelevance();
