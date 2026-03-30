// check_similarity.js
const { db } = require('./src/config/database');
require('dotenv').config();

async function check() {
    const query = 'shoes';
    const entity = 'shoe';
    const target = 'shirt';
    const synonyms = ['sneaker', 'kick', 'footwear'];

    const sql = `
        SELECT 
            word_similarity($1, $3) as sim_query,
            word_similarity($2, $3) as sim_entity,
            word_similarity('sneaker', $3) as sim_syn1,
            word_similarity('kick', $3) as sim_syn2,
            word_similarity('footwear', $3) as sim_syn3,
            $1 <% $3 as passes_query,
            $2 <<% $3 as passes_entity_strict
    `;
    
    const res = await db.query(sql, [query, entity, target]);
    console.log(res.rows[0]);
}

check().catch(console.error);
