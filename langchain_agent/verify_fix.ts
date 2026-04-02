import { hybridSearchTool } from "./src/tools/vector_search.tool";

async function verify() {
    try {
        console.log("Verifying Hybrid Search Image Support...");
        const resultString = await (hybridSearchTool as any).func({ query: "dress", limit: 3 });
        const results = JSON.parse(resultString);
        
        if (results.status === "no_results") {
            console.log("No results found. This is expected if the database is empty of 'dress' products.");
            process.exit(0);
        }

        if (Array.isArray(results)) {
            console.log(`Success! Fetched ${results.length} results.`);
            results.forEach((row, idx) => {
                console.log(`[${idx}] Product: ${row.name}`);
                console.log(`    Image Path: ${row.image_path}`);
                console.log(`    Full Image URL: ${row.image}`);
            });
        } else {
            console.error("Unexpected result format:", results);
        }
        
        process.exit(0);
    } catch (error) {
        console.error("Verification failed:", error);
        process.exit(1);
    }
}

verify();
