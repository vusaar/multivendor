<?php

use App\Models\Product;
use App\Http\Controllers\Api\StorefrontProductController;
use Illuminate\Http\Request;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Search Relevance Filtering (In-Process)...\n";

$controller = new StorefrontProductController();
$request = new Request(['product' => 'shirt', 'per_page' => 10]);

try {
    $response = $controller->search($request);
    $data = json_decode($response->getContent(), true);
    $products = $data['data'] ?? [];

    echo "Search Results for 'shirt':\n";
    echo "--------------------------------------------------\n";
    foreach ($products as $p) {
        $relevant = $p['is_highly_relevant'] ? "[HIGHLY RELEVANT]" : "[SUGGESTION]";
        echo sprintf("%-18s | %-25s | Score: %-10.4f | %s\n", 
            $relevant, 
            $p['name'], 
            $p['similarity_score'],
            $p['category']['name'] ?? 'N/A'
        );
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
