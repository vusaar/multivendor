import { execSync } from 'child_process';
import * as path from 'path';

const brands = [
    { query: "jordan sneakers", count: 20, catId: 107 },
    { query: "lacoste sneakers", count: 20, catId: 107 },
    { query: "fila sneakers", count: 20, catId: 107 },
    { query: "louis vuitton sneakers", count: 15, catId: 107 },
    { query: "nike high top sneakers", count: 20, catId: 107 },
    { query: "adidas performance shoes", count: 20, catId: 107 },
    { query: "puma drift cat", count: 15, catId: 107 },
    { query: "skechers walking shoes", count: 15, catId: 107 },
    { query: "dr martens boots", count: 15, catId: 106 },
    { query: "timberland boots", count: 15, catId: 106 },
    { query: "birkenstock sandals", count: 15, catId: 125 },
    { query: "brooks running shoes", count: 15, catId: 125 }
];

async function run() {
    for (const brand of brands) {
        console.log(`\n=========================================`);
        console.log(`[BULK] Processing: ${brand.query.toUpperCase()}`);
        console.log(`=========================================\n`);

        try {
            // Escape double quotes for shell
            const escapedQuery = brand.query.replace(/"/g, '\\"');
            const cmd = `npx ts-node src/scripts/prepare_ingestion.ts "${escapedQuery}" ${brand.count} ${brand.catId}`;
            
            execSync(cmd, { 
                stdio: 'inherit',
                cwd: path.join(__dirname, '../..')
            });
            
            // Short delay to avoid pounding the LLM/API too fast
            await new Promise(resolve => setTimeout(resolve, 2000));
            
        } catch (error) {
            console.error(`[BULK] Error processing ${brand.query}:`, error);
        }
    }
    
    console.log(`\n[BULK DONE] All batches prepared.`);
}

run();
