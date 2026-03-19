<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$evalSet = [
    ['query' => 'mascara', 'expected_ids' => [1]],
    ['query' => 'makeup kit', 'expected_ids' => [2, 3]],
    ['query' => 'red makeup', 'expected_ids' => [4, 5]],
    ['query' => 'perfume', 'expected_ids' => [6, 7, 8, 9, 10]],
    ['query' => 'Calvin Klein', 'expected_ids' => [6]],
    ['query' => 'expensive scent', 'expected_ids' => [7, 8, 10]],
    ['query' => 'eye shadow', 'expected_ids' => [2]],
    ['query' => 'matte finish powder', 'expected_ids' => [3]],
    ['query' => 'Gucci perfume', 'expected_ids' => [10]],
    ['query' => 'floral fragrance', 'expected_ids' => [10, 9, 8]],
];

echo "--- SEMANTIC SEARCH ACCURACY EVALUATION ---\n";
echo "Metric: Recall@3 (Is expected in top 3?) | MRR (Rank influence)\n\n";

$totalRecall = 0;
$totalMRR = 0;
$agentUrl = "http://localhost:3002/api/search";

foreach ($evalSet as $test) {
    $query = $test['query'];
    $expectedIds = $test['expected_ids'];

    try {
        $response = Http::post($agentUrl, ['query' => $query]);
        if (!$response->successful()) {
            echo "QUERY [$query]: FAILED (Agent Error)\n";
            continue;
        }

        $results = $response->json()['data']['results'] ?? [];
        if (isset($results['status']) && $results['status'] === 'no_results') {
            $results = [];
        }

        $foundIds = array_column(array_slice($results, 0, 10), 'id');
        
        // Calculate Recall@3
        $top3 = array_slice($foundIds, 0, 3);
        $recallAt3 = 0;
        foreach ($expectedIds as $id) {
            if (in_array($id, $top3)) {
                $recallAt3 = 1;
                break;
            }
        }
        $totalRecall += $recallAt3;

        // Calculate Reciprocal Rank
        $rr = 0;
        foreach ($foundIds as $rank => $id) {
            if (in_array($id, $expectedIds)) {
                $rr = 1 / ($rank + 1);
                break;
            }
        }
        $totalMRR += $rr;

        $resultsStr = count($foundIds) > 0 ? implode(', ', array_slice($foundIds, 0, 5)) : "NONE";
        echo "QUERY [$query]: Top IDs: [$resultsStr] | Recall@3: $recallAt3 | RR: " . round($rr, 2) . "\n";

    } catch (\Exception $e) {
        echo "QUERY [$query]: EXCEPTION: " . $e->getMessage() . "\n";
    }
}

$count = count($evalSet);
echo "\n--- FINAL SCORE ---\n";
echo "Average Recall@3: " . round($totalRecall / $count, 2) . "\n";
echo "Mean Reciprocal Rank (MRR): " . round($totalMRR / $count, 2) . "\n";
