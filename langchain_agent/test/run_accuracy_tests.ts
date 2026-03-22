import { processUserQuery } from '../src/services/search.agent';
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
        max_results?: number;
        min_confidence?: number;
        max_confidence?: number;
    }
}

async function runTests() {
    console.log("\n🚀 STARTING SEARCH ACCURACY TEST SUITE");
    console.log("==================================================");

    const casesPath = path.join(__dirname, 'accuracy_cases.json');
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
            // Mock userId for testing
            const results = await processUserQuery(test.query, "test_user");
            
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
                const foundIds = Array.isArray(results) ? results.map((r: any) => parseInt(r.id)) : [];
                const missing = test.expected.must_contain.filter(id => !foundIds.includes(id));
                const allFound = missing.length === 0;
                checks.push({ 
                    name: `Must Contain IDs ${test.expected.must_contain.join(',')}`, 
                    pass: allFound, 
                    actual: missing.length > 0 ? `Missing: ${missing.join(',')}` : 'All Found' 
                });
                if (!allFound) passed = false;
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

    if (summary.failed > 0) {
        process.exit(1);
    } else {
        process.exit(0);
    }
}

runTests();
