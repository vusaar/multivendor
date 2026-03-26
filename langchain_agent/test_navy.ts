import { processUserQuery } from './src/services/search.agent';
import * as fs from 'fs';

async function run() {
    try {
        const results = await processUserQuery("navy sneakers", "test_navy_" + Date.now());
        const cleanResults = results.map((r: any) => ({id: r.id, name: r.name, score: r.rrf_score || r.final_score, context: r.search_context}));
        fs.writeFileSync('navy_results.json', JSON.stringify(cleanResults, null, 2));
    } catch (err: any) {
        fs.writeFileSync('navy_results.json', JSON.stringify({error: err.message}));
    }
    process.exit(0);
}

run();
