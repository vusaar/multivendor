import { processUserQuery } from "./src/services/search.agent"
import dotenv from "dotenv"

dotenv.config()

async function testAgentRelevance(userQuery: string) {
    console.log(`\n--- Testing Query: "${userQuery}" ---`)
    const results = await processUserQuery(userQuery, "test_user")

    if (Array.isArray(results)) {
        console.log(`Top 10 Results:`)
        results.slice(0, 10).forEach((r, i) => {
            console.log(`${i + 1}. [ID: ${r.id}] [RRF: ${parseFloat(r.rrf_score).toFixed(4)}] ${r.name} (Price: ${r.price})`)
        })
    } else {
        console.log("Result:", results)
    }
}

async function runTests() {
    try {
        await testAgentRelevance("gents tshirt")
        process.exit(0)
    } catch (err) {
        console.error(err)
        process.exit(1)
    }
}

runTests()
