<?php

use App\Models\Product;
use App\Jobs\GenerateProductEmbedding;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Starting Global Embedding Generation...\n";

$products = Product::all();
$total = count($products);
echo "Total products: $total\n";

foreach ($products as $index => $product) {
    echo "[" . ($index + 1) . "/$total] Dispatching job for: {$product->name} (ID: {$product->id})\n";
    GenerateProductEmbedding::dispatch($product);
}

echo "\nDone dispatching all jobs. (Since QUEUE_CONNECTION=sync, they should be processed now)\n";
