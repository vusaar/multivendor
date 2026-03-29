import { processUserQuery } from './src/services/search.agent';
import { db } from './src/config/database';
import dotenv from 'dotenv';
import * as fs from 'fs';

dotenv.config();

async function debugQuery(query: string) {
    console.log(`\n=== DEBUGGING QUERY: "${query}" ===`);
    try {
        const results = await processUserQuery(query, "debug_user_" + Date.now());
        let log = `\n=== DEBUGGING QUERY: "${query}" ===\n`;
        log += `Results Found: ${results.length}\n`;
        
        results.slice(0, 5).forEach((r: any, i: number) => {
            log += `${i+1}. [ID: ${r.id}] ${r.name} - Score: ${r.rrf_score}\n`;
        });

        // Check if specific IDs are present
        const targetIds = [15, 60, 2];
        targetIds.forEach(tid => {
            const index = results.findIndex((r: any) => parseInt(r.id) === tid);
            if (index !== -1) {
                log += `🎯 Target ID ${tid} found at position ${index + 1} with score ${results[index].rrf_score}\n`;
            } else {
                log += `❌ Target ID ${tid} NOT found in results.\n`;
            }
        });
        console.log(log);
        fs.appendFileSync('debug_output.txt', log);
    } catch (error: any) {
        const errorLog = `Error debugging query "${query}": ${error.message}\n`;
        console.error(errorLog);
        fs.appendFileSync('debug_output.txt', errorLog);
    }
}

async function run() {
    if (fs.existsSync('debug_output.txt')) fs.unlinkSync('debug_output.txt');
    await debugQuery("golf tshirts");
    process.exit(0);
}

run();
