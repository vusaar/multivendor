<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SearchLog;

$log = SearchLog::where('query', 'running shoes')->latest()->first();
if ($log) {
    echo "Query: " . $log->query . "\n";
    echo "Intent: " . json_encode($log->intent, JSON_PRETTY_PRINT) . "\n";
    
    $results = is_string($log->results) ? json_decode($log->results, true) : $log->results;
    
    if (is_array($results)) {
        echo "Top 10 Results:\n";
        $top10 = array_slice($results, 0, 10);
        foreach ($top10 as $i => $item) {
            echo ($i + 1) . ". [ID: " . ($item['id'] ?? '??') . "] " . ($item['name'] ?? '??') . " (Score: " . ($item['rrf_score'] ?? '0') . ")\n";
        }
        
        // Find ID 59 specifically
        $rank = null;
        foreach ($results as $i => $item) {
            if (($item['id'] ?? '') == 59) {
                $rank = $i + 1;
                break;
            }
        }
        echo "\nSneaker (ID 59) Rank: " . ($rank ?? 'NOT IN RESULTS') . "\n";
    } else {
        echo "Results is not an array.\n";
    }
} else {
    echo "No search log found for 'running shoes'.\n";
}
