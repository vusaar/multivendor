import { executeHybridSearch } from "./src/tools/vector_search.tool"
import { embeddingsService } from "./src/services/embeddings.service"
import { db } from "./src/config/database"
import dotenv from "dotenv"

dotenv.config()

async function testRelevance(query: string) {
    console.log(`\n--- Testing Query: "${query}" ---`)
    const embedding = await embeddingsService.generateEmbedding(query)
    const results = await executeHybridSearch({
        query,
        embedding,
        limit: 10
    })

    console.log("Top 10 Results:")
    results.forEach((r, i) => {
        console.log(`${i + 1}. [Score: ${parseFloat(r.rrf_score).toFixed(4)}] ${r.name} (Price: ${r.price})`)
    })
}

async function runTests() {
    try {
        await testRelevance("gents tshirt")
        await testRelevance("blue jeans")
        process.exit(0)
    } catch (err) {
        console.error(err)
        process.exit(1)
    }
}

runTests()
