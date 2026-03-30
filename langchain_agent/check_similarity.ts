import { db } from './src/config/database';
import dotenv from 'dotenv';
dotenv.config();

async function check() {
    const query = 'shoes';
    const entity = 'shoe';
    const target = 'shirt';

    const sql = `
        SELECT 
            word_similarity($1, $2) as sim_query_target,
            word_similarity($1, 'shoe') as sim_query_shoe,
            word_similarity($1, 'shirt') as sim_query_shirt,
            word_similarity('footwear', 'shirt') as sim_footwear_shirt,
            'shoes' <% 'shirt' as passes_05,
            'shoes' <<% 'shirt' as passes_strict_05
    `;
    
    const res = await db.query(sql, [query, target]);
    console.log(JSON.stringify(res.rows[0], null, 2));
}

check().catch(console.error).finally(() => process.exit(0));
