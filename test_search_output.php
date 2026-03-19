<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$query = "mans shirts";
echo "Testing Search for: $query\n";

$searchService = app(\App\Services\SearchAgentService::class);
echo "Calling SearchAgentService directly...\n";
$results = $searchService->search($query);
echo "Results from Agent:\n" . var_export($results, true) . "\n\n";

$response = Http::post("http://127.0.0.1:8000/api/storefront/products/search", [
    'product' => $query
]);

if ($response->successful()) {
    $json = $response->json();
    $p = $json['data'][0] ?? null;
    if ($p) {
        echo "RESULT_FOUND\n";
        echo "ID: " . $p['id'] . "\n";
        echo "SCORE_VAL: " . var_export($p['similarity_score'], true) . "\n";
        echo "SCORE_TYPE: " . gettype($p['similarity_score']) . "\n";
    } else {
        echo "NO_RESULTS_IN_JSON\n";
    }
}
 else {
    echo "FAILED: " . $response->status() . "\n";
    echo $response->body() . "\n";
}
