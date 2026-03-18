import { executeHybridSearch } from './src/tools/vector_search.tool';
import { embeddingsService } from './src/services/embeddings.service';
import dotenv from 'dotenv';

dotenv.config();

async function verify() {
    const query = "television";
    console.log(`Verifying exclusion for: "${query}"`);

    try {
        const embedding = await embeddingsService.generateEmbedding(query);
        const results = await executeHybridSearch({
            query,
            embedding,
            limit: 10
        });

        console.log(`Results found: ${results.length}`);
        results.forEach((r: any, i: number) => {
            console.log(`${i + 1}. ${r.name} (Score: ${r.rrf_score})`);
        });

        process.exit(0);
    } catch (error) {
        console.error("Verification failed:", error);
        process.exit(1);
    }
}

verify();
