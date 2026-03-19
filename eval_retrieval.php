<?php

use App\Models\Product;
use Illuminate\Support\Facades\Http;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

/**
 * Retrieval Evaluation Script
 * Metrics: Precision@K, Recall
 */

$testCases = [
    [
        'query' => 'shirt',
        'expected_keywords' => ['shirt'],
        'min_expected_count' => 1
    ],
    [
        'query' => 'lipstick',
        'expected_keywords' => ['lipstick'],
        'min_expected_count' => 1
    ],
    [
        'query' => 'perfume',
        'expected_keywords' => ['eau de', 'CK', 'Dior'],
        'min_expected_count' => 1
    ]
];

$results = [];
$agentUrl = 'http://localhost:3002/api/search';

echo "Starting Retrieval Evaluation...\n";
echo "Agent URL: $agentUrl\n\n";

foreach ($testCases as $case) {
    echo "Query: \"{$case['query']}\"\n";
    
    try {
        $response = Http::post($agentUrl, [
            'query' => $case['query'],
            'limit' => 5
        ]);

        if (!$response->successful()) {
            echo "  [ERROR] Agent request failed.\n";
            continue;
        }

        $data = $response->json();
        $matches = $data['data']['results'] ?? [];
        
        $hits = 0;
        $matchedProducts = [];

        foreach ($matches as $match) {
            $product = Product::find($match['id']);
            if (!$product) continue;

            $text = strtolower($product->name . ' ' . $product->description);
            $allKeywordsFound = true;
            foreach ($case['expected_keywords'] as $keyword) {
                if (!str_contains($text, strtolower($keyword))) {
                    $allKeywordsFound = false;
                    break;
                }
            }

            if ($allKeywordsFound) {
                $hits++;
                $matchedProducts[] = "[HIT] {$product->name} (ID: {$product->id}, Score: {$match['rrf_score']})";
            } else {
                $matchedProducts[] = "[MISS] {$product->name} (ID: {$product->id}, Score: {$match['rrf_score']})";
            }
        }

        $precision = count($matches) > 0 ? ($hits / count($matches)) * 100 : 0;
        
        echo "  Precision@5: $precision%\n";
        foreach ($matchedProducts as $mp) echo "    $mp\n";
        
        $results[] = [
            'query' => $case['query'],
            'precision' => $precision,
            'hits' => $hits,
            'total' => count($matches)
        ];

    } catch (\Exception $e) {
        echo "  [EXCEPTION] " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Generate Report
$report = "# Semantic Search Retrieval Evaluation Report\n\n";
$report .= "Date: " . date('Y-m-d H:i:s') . "\n\n";
$report .= "| Query | Precision@5 | Hits | Total Returned |\n";
$report .= "| :--- | :--- | :--- | :--- |\n";

$totalPrecision = 0;
foreach ($results as $r) {
    $report .= "| {$r['query']} | {$r['precision']}% | {$r['hits']} | {$r['total']} |\n";
    $totalPrecision += $r['precision'];
}

$avgPrecision = count($results) > 0 ? $totalPrecision / count($results) : 0;
$report .= "\n**Average Precision@5: " . number_format($avgPrecision, 2) . "%**\n";

file_put_contents('retrieval_eval_report.md', $report);
echo "Report generated: retrieval_eval_report.md\n";
