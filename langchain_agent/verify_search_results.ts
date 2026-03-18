import { processUserQuery } from "./src/services/search.agent"
import dotenv from "dotenv"

dotenv.config()

async function testQuery(userQuery: string) {
    console.log(`\n--- Testing Query: "${userQuery}" ---`)
    try {
        const results = await processUserQuery(userQuery, "test_user")

        if (Array.isArray(results)) {
            console.log(`Found ${results.length} results. Top 20:`)
            results.slice(0, 20).forEach((r, i) => {
                console.log(`${i + 1}. [ID: ${r.id}] [RRF: ${parseFloat(r.rrf_score).toFixed(4)}] ${r.name} (Price: ${r.price})`)
            })
        } else {
            console.log("Result:", JSON.stringify(results, null, 2))
        }
    } catch (err) {
        console.error("Error during search:", err)
    }
}

async function run() {
    await testQuery("gents tshirt")
    process.exit(0)
}

run()
