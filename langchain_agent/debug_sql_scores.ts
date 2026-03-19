import { db } from "./src/config/database"
import dotenv from "dotenv"

dotenv.config()

async function debugScores() {
    const query = "gents tshirt";
    const entity = "Tshirt";
    const categories = ["Men", "gents"];
    
    // Exact logic from vector_search.tool.ts
    const weightRoot = 15.0;
    const weightSub = 5.0;
    const weightEntity = 7.0;
    const weightAttr = 3.0;

    let weightedScoreSql = `(similarity(products.name, $1) * 3 + similarity(products.search_context, $1))`;
    const sqlParams: any[] = [query];
    let caseSelects = "";

    if (entity) {
        sqlParams.push(entity.toLowerCase());
        const pIdx = sqlParams.length;
        weightedScoreSql += ` + (CASE WHEN LOWER(products.name) ILIKE '%' || $${pIdx} || '%' THEN ${weightEntity} ELSE 0 END)`;
        caseSelects += `, (CASE WHEN LOWER(products.name) ILIKE '%' || $${pIdx} || '%' THEN ${weightEntity} ELSE 0 END) as ent_score`;
    }

    if (categories) {
        categories.forEach((cat, i) => {
            sqlParams.push(cat.toLowerCase());
            const pIdx = sqlParams.length;
            const caseSql = `(CASE 
                WHEN LOWER(products.search_context) ILIKE '%categorypath: %' || $${pIdx} || '%' THEN ${weightRoot}
                WHEN LOWER(products.search_context) ILIKE '% > %' || $${pIdx} || '%' THEN ${weightSub}
                WHEN LOWER(products.search_context) ILIKE '% | %' || $${pIdx} || '%' THEN ${weightSub}
                ELSE 0 END)`;
            weightedScoreSql += ` + ${caseSql}`;
            caseSelects += `, ${caseSql} as cat_score_${i}`;
        });
    }

    const sql = `
        SELECT id, name, search_context ${caseSelects},
               (${weightedScoreSql}) as base_weighted_score,
               (similarity(products.name, $1) * 3 + similarity(products.search_context, $1)) as sim_score
        FROM products
        WHERE id IN (14, 17, 38, 12, 13, 15, 9, 10)
    `;

    const res = await db.query(sql, sqlParams);
    console.log("Debug Scores for 'gents tshirt':");
    res.rows.forEach(r => {
        let scores = `E:${r.ent_score}|C0:${r.cat_score_0}|C1:${r.cat_score_1}`;
        console.log(`ID:${r.id}|W:${r.base_weighted_score.toFixed(2)}|S:${r.sim_score.toFixed(2)}|${scores}|Name:${r.name}`);
    });
    process.exit(0);
}

debugScores();
