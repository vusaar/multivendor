import { processUserQuery } from './src/services/search.agent';
import { db } from './src/config/database';
import dotenv from 'dotenv';
dotenv.config();

const queries = [
    "ladies shirt for summer",
    "gents casual wear",
    "gucci apparel",
    "floral top",
    "smartwatch"
];

import fs from 'fs';

async function runTests() {
    let output = '';
    const log = (msg: string) => { output += msg + '\n'; };

    for (const q of queries) {
        log(`\n==============================================`);
        log(`QUERY: "${q}"`);
        log(`==============================================`);
        
        try {
            const results = await processUserQuery(q, "test-user-1");
            
            if (results && results.status === "no_results") {
                log(`Status: NO RESULTS`);
            } else if (Array.isArray(results) && results.length > 0) {
                log(`Found ${results.length} results. Top 5:`);
                results.slice(0, 5).forEach((r: any, idx: number) => {
                    const ctxMatch = r.search_context ? r.search_context.replace(/\n|\|/g, " ").substring(0, 80) : "";
                    log(`  ${idx + 1}. [ID: ${r.id}] ${r.name}`);
                    const score = r.rrf_score ? parseFloat(r.rrf_score).toFixed(4) : "?";
                    log(`     Score: ${score} | Context: ${ctxMatch}...`);
                });
            } else {
                log(`No results array returned.`);
            }
        } catch(e: any) {
            log(`Error testing query: ${e.message}`);
        }
    }
    fs.writeFileSync('test_suite_log.txt', output);
    process.exit(0);
}

runTests();
