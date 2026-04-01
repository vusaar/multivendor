import { processUserQuery } from '../src/services/search.agent';
import { SEARCH_CONFIG } from '../src/config/search';
import * as fs from 'fs';
import * as path from 'path';

interface TestCase {
    query: string;
    description: string;
    expected: {
        entity?: string;
        categories?: string[];
        attributes?: string[];
        max_price?: number;
        must_contain?: number[]; // IDs
        must_not_contain?: number[]; // IDs
        must_not_contain_verified?: number[]; // IDs
        must_contain_at_least_one?: number[]; // IDs
        max_results?: number;
        min_confidence?: number;
        max_confidence?: number;
    }
}

async function runTests() {
    console.log("\n🚀 STARTING SEARCH ACCURACY TEST SUITE");
    console.log("==================================================");

    const customPath = process.argv[2];
    const casesPath = customPath ? path.resolve(customPath) : path.join(__dirname, 'accuracy_cases.json');
    console.log(`🔍 Using test cases from: ${casesPath}`);
    const cases: TestCase[] = JSON.parse(fs.readFileSync(casesPath, 'utf8'));

    const summary = {
        total: cases.length,
        passed: 0,
        failed: 0,
        results: [] as any[]
    };

    for (const test of cases) {
        console.log(`\n🔍 TEST: "${test.query}" - ${test.description}`);
        
        try {
            // Use unique userId for each test to avoid session leakage/bypass logic
            const testUserId = `test_user_${cases.indexOf(test)}_${Date.now()}`;
            const results = await processUserQuery(test.query, testUserId);
            
            // We need the intent from the last search log or by calling the AI again
            // For this suite, we'll focus on the actual RESULTS and trust processUserQuery (which logs its intent)
            
            const checks = [];
            let passed = true;

            // 1. Result Count Check
            if (test.expected.max_results !== undefined) {
                const countMatch = results.length <= test.expected.max_results;
                checks.push({ name: `Result Count <= ${test.expected.max_results}`, pass: countMatch, actual: results.length });
                if (!countMatch) passed = false;
            }

            // 2. Must Contain Check
            if (test.expected.must_contain) {
                const foundIds = Array.isArray(results) ? results.map((r: any) => parseInt(r.id || r.product_id)) : [];
                const missing = test.expected.must_contain.filter(id => !foundIds.includes(id));
                const allFound = missing.length === 0;
                checks.push({ 
                    name: `Must Contain IDs ${test.expected.must_contain.join(',')}`, 
                    pass: allFound, 
                    actual: missing.length > 0 ? `Missing: ${missing.join(',')} (Found: ${foundIds.join(',')})` : 'All Found' 
                });
                if (!allFound) passed = false;
            }

            // 2.1 Must Not Contain Check
            if (test.expected.must_not_contain) {
                const foundIds = Array.isArray(results) ? results.map((r: any) => parseInt(r.id || r.product_id)) : [];
                const forbiddenFound = test.expected.must_not_contain.filter(id => foundIds.includes(id));
                const noneFound = forbiddenFound.length === 0;
                checks.push({ 
                    name: `Must NOT Contain IDs ${test.expected.must_not_contain.join(',')}`, 
                    pass: noneFound, 
                    actual: noneFound ? 'None Found' : `Forbidden items found: ${forbiddenFound.join(',')}` 
                });
                if (!noneFound) passed = false;
            }

            // 2.1b Must Not Contain in Verified Check
            if (test.expected.must_not_contain_verified) {
                const verifiedThreshold = SEARCH_CONFIG.THRESHOLD_VERIFIED;
                const verified = Array.isArray(results) ? results.filter((r: any) => 
                    parseFloat(r.rrf_score || r.score || "0") >= verifiedThreshold &&
                    r.is_direct_match === true
                ) : [];
                const foundIds = verified.map((r: any) => parseInt(r.id || r.product_id));
                const forbiddenFound = test.expected.must_not_contain_verified.filter(id => foundIds.includes(id));
                const noneFound = forbiddenFound.length === 0;
                checks.push({ 
                    name: `Must NOT Contain Verified IDs ${test.expected.must_not_contain_verified.join(',')}`, 
                    pass: noneFound, 
                    actual: noneFound ? 'None Found' : `Forbidden VERIFIED items found: ${forbiddenFound.join(',')}` 
                });
                if (!noneFound) passed = false;
            }

            // 2.2 Must Contain At Least One Check
            if (test.expected.must_contain_at_least_one) {
                const foundIds = Array.isArray(results) ? results.map((r: any) => parseInt(r.id || r.product_id)) : [];
                const foundMatch = test.expected.must_contain_at_least_one.some(id => foundIds.includes(id));
                checks.push({ 
                    name: `Must Contain at least one of ${test.expected.must_contain_at_least_one.join(',')}`, 
                    pass: foundMatch, 
                    actual: foundMatch ? 'Match Found' : 'None Found' 
                });
                if (!foundMatch) passed = false;
            }

            // 3. Confidence Threshold Check (Verification vs Suggestion)
            if (Array.isArray(results) && results.length > 0) {
                const topScore = parseFloat(results[0].rrf_score || "0");
                
                if (test.expected.min_confidence !== undefined) {
                    const enough = topScore >= test.expected.min_confidence;
                    checks.push({ name: `Score >= ${test.expected.min_confidence} (Verified)`, pass: enough, actual: topScore });
                    if (!enough) passed = false;
                }
                
                if (test.expected.max_confidence !== undefined) {
                    const lowEnough = topScore < test.expected.max_confidence;
                    checks.push({ name: `Score < ${test.expected.max_confidence} (Suggestion)`, pass: lowEnough, actual: topScore });
                    if (!lowEnough) passed = false;
                }
            }

            if (passed) summary.passed++;
            else summary.failed++;

            summary.results.push({ query: test.query, passed, checks });

            checks.forEach(c => {
                console.log(`   ${c.pass ? '✅' : '❌'} ${c.name} (Actual: ${c.actual})`);
            });

        } catch (error: any) {
            console.error(`   ❌ CRASH: ${error.message}`);
            summary.failed++;
            summary.results.push({ query: test.query, passed: false, error: error.message });
        }
    }

    console.log("\n==================================================");
    console.log(`📊 FINAL REPORT: ${summary.passed}/${summary.total} PASSED`);
    console.log("==================================================");

    const summaryPath = path.join(process.cwd(), 'test/test_summary.json');
    console.log(`\n💾 Saving results to: ${summaryPath}`);
    fs.writeFileSync(summaryPath, JSON.stringify(summary, null, 2));

    if (summary.failed > 0) {
        process.exit(1);
    } else {
        process.exit(0);
    }
}

runTests();
