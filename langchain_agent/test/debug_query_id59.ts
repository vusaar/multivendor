import { executeHybridSearch } from '../src/tools/vector_search.tool';
import { embeddingsService } from '../src/services/embeddings.service';

async function debugID59() {
    const query = "blue adidas running shoes";
    const embedding = await embeddingsService.generateEmbedding(query);
    
    console.log(`\n🔍 DEBUGGING SCORES for: "${query}"`);
    try {
        const rows = await executeHybridSearch({
            query: "running shoes",
            brand: "adidas", // This is what the agent extracts
            attributes: ["blue"],
            target_category_slug: "men-footwear", // Taxonomy boost
            embedding,
            limit: 50
        } as any);

        const id59 = rows.find((r: any) => r.id === 59);
        if (id59) {
            console.log("\n✅ Product ID 59 Found in SQL results!");
            console.log("-----------------------------------------");
            console.log(JSON.stringify(id59, null, 2));
            console.log("-----------------------------------------");
        } else {
            console.log("\n❌ Product ID 59 NOT FOUND in SQL top 50 results.");
            const top5 = rows.slice(0, 5);
            console.log("Top 5 Results:");
            top5.forEach((r: any, i: number) => {
                console.log(`[${i+1}] ID: ${r.id} | NAME: ${r.name} | SCORE: ${r.rrf_score.toFixed(2)}`);
            });
        }
    } catch (error: any) {
        console.error("ERROR:", error);
    }
    process.exit(0);
}

debugID59();
