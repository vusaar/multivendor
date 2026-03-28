import { processUserQuery } from './src/services/search.agent';

async function testSearch() {
    const query = process.argv[2] || 'long sleeved ladies tops';
    const userId = process.argv[3] || 'verification_user';
    console.log(`\n🔍 SEARCHING FOR: "${query}" (User: ${userId})`);
    console.log("==================================================");

    try {
        const results = await processUserQuery(query, userId);
        if (results && results.length > 0) {
            results.slice(0, 5).forEach((item: any, i: number) => {
                console.log(`${i+1}. [ID: ${item.id}] ${item.name}`);
                console.log(`   💰 Price: ${item.price} | Score: ${item.rrf_score.toFixed(2)}`);
                console.log(`   🏷️ Category: ${item.category_id}`);
                console.log("---");
            });
        } else {
            console.log("❌ No results found.");
        }
    } catch (error: any) {
        console.error(`❌ ERROR: ${error.message}`);
    }
    process.exit(0);
}

testSearch();
