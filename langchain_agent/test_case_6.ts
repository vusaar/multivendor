import { processUserQuery } from './src/services/search.agent';

async function run() {
    console.log(`🔍 LOGGING FROM: ${require.resolve('./src/services/search.agent')}`);
    console.log("🧪 RUNNING ISOLATED CASE 6: 'Cotton hoodie'");
    console.log("==================================================");
    
    // Use a fresh ID to avoid any session bypass
    const userId = `test_case_6_${Date.now()}`;
    const results = await processUserQuery("Cotton hoodie", userId);
    
    if (results && results.length > 0) {
        console.log(`✅ Success! Found ${results.length} results.`);
        console.log(`Top result: ${results[0].name} (ID: ${results[0].id})`);
        console.log(`Score: ${results[0].rrf_score.toFixed(2)}`);
    } else {
        console.log("❌ Failed! No results found.");
    }
    process.exit(0);
}

run();
