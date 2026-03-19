<?php

use App\Models\Product;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing product save lifecycle...\n";

$product = Product::first();
if (!$product) {
    echo "No products found to test.\n";
    exit;
}

echo "Current Product: {$product->name} (ID: {$product->id})\n";
echo "Attempting to save...\n";

$start = microtime(true);
$product->name = trim($product->name) . ' ';
try {
    $product->save();
    $end = microtime(true);
    echo "Saved successfully in " . round($end - $start, 2) . " seconds!\n";
    
    // Check if job updated context/embedding
    $product->refresh();
    if (!empty($product->search_context)) {
        echo "Success: search_context is populated.\n";
    } else {
        echo "Warning: search_context is empty (Agent might still be processing or failed).\n";
    }
} catch (\Exception $e) {
    echo "Error saving product: " . $e->getMessage() . "\n";
}
