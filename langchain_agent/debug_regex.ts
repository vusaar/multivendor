import { db } from "./src/config/database"
import dotenv from "dotenv"

dotenv.config()

async function debugRegex() {
    const categories = ["men", "gents"];
    const productId = 15; // Known Men's Tshirt

    console.log("Testing Regex for ID 15 matches categories:", categories);

    for (const c of categories) {
        // Pattern 1: \y
        const sql1 = `SELECT id, name, search_context, 
                      (LOWER(search_context) ~* ('categorypath:.*\\y' || $2 || '\\y')) as matches
                      FROM products WHERE id = $1`;
        
        // Pattern 2: Word boundaries without \y
        const sql2 = `SELECT id, name, search_context, 
                      (LOWER(search_context) ~* ('categorypath:.*(^|[^a-z0-9])' || $2 || '($|[^a-z0-9])')) as matches
                      FROM products WHERE id = $1`;

        try {
            const res1 = await db.query(sql1, [productId, c]);
            console.log(`Pattern \\y with "${c}":`, res1.rows[0]?.matches);
            if (res1.rows[0]) console.log("Context:", res1.rows[0].search_context);

            const res2 = await db.query(sql2, [productId, c]);
            console.log(`Pattern [^a-z] with "${c}":`, res2.rows[0]?.matches);
        } catch (e: any) {
            console.error(`Error with "${c}":`, e.message);
        }
    }
    
    // Test with Women's product
    const womenId = 17;
    console.log("\nTesting Regex for ID 17 (Women's) with 'men':");
    const sql3 = `SELECT id, name, search_context, 
                  (LOWER(search_context) ~* ('categorypath:.*\\y' || $2 || '\\y')) as matches
                  FROM products WHERE id = $1`;
    const res3 = await db.query(sql3, [womenId, "men"]);
    console.log(`Pattern \\y with "men" on Women's item:`, res3.rows[0]?.matches);

    process.exit(0);
}

debugRegex();
