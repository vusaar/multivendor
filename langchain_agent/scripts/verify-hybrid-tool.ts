import { hybridSearchTool } from '../src/tools/vector_search.tool';
import dotenv from 'dotenv';

dotenv.config();

async function verify() {
    console.log("--- Direct Tool Verification ---\n");

    const query = "short sleeve shirts for men";
    console.log(`Testing Query: "${query}"`);

    try {
        const result = await (hybridSearchTool as any).func({ query, limit: 100 });
        const parsed = JSON.parse(result);

        if (Array.isArray(parsed)) {
            console.table(parsed.map(p => ({
                id: p.id,
                name: p.name,
                price: p.price,
                score: parseFloat(p.rrf_score).toFixed(4),
                description: p.description
            })));
        } else {
            console.log(JSON.stringify(parsed, null, 2));
        }
    } catch (e) {
        console.error("Error:", e);
    }

    process.exit(0);
}

verify();
