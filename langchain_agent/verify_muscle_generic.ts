import { executeHybridSearch } from "./src/tools/vector_search.tool"
import { embeddingsService } from "./src/services/embeddings.service"
import dotenv from "dotenv"

dotenv.config()

async function testMuscle(query: string, categories: string[]) {
    console.log(`\n--- Testing SQL Muscle: Query="${query}", Categories=${JSON.stringify(categories)} ---`)
    try {
        const embedding = await embeddingsService.generateEmbedding(query)
        const results = await executeHybridSearch({
            query,
            categories,
            embedding,
            limit: 10,
            offset: 0
        })

        if (Array.isArray(results)) {
            console.log(`Found ${results.length} results. Top 5:`)
            results.slice(0, 5).forEach((r, i) => {
                console.log(`${i + 1}. [ID: ${r.id}] [RRF: ${parseFloat(r.rrf_score).toFixed(4)}] ${r.name}`)
            })
            
            // Check if any results got the boost (> 1.0)
            const boosted = results.filter(r => parseFloat(r.rrf_score) > 1.0)
            console.log(`Boosted results: ${boosted.length}`)
        }
    } catch (err) {
        console.error("Error:", err)
    }
}

async function run() {
    // Testing with Beauty category to prove it's not just for Men/Women
    await testMuscle("mascara", ["beauty"])
    process.exit(0)
}

run()
