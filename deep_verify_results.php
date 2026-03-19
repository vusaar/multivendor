<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

// These are the IDs from the recent verify_search_results.ts run for "gents tshirt"
$top_ids = [15, 12, 41, 17, 42, 43, 18, 13, 38, 16, 51, 19, 20, 21, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 14];

$products = Product::with(['category', 'category.parent'])->whereIn('id', $top_ids)->get();

// Sort according to order in $top_ids
$products = $products->sortBy(function($model) use ($top_ids) {
    return array_search($model->id, $top_ids);
});

echo "Relevance check for 'gents tshirt':\n";
foreach ($products as $p) {
    $parent = ($p->category && $p->category->parent) ? $p->category->parent->name : 'None';
    $cat = $p->category ? $p->category->name : 'None';
    echo "Rank: " . (array_search($p->id, $top_ids) + 1) . " | ID: {$p->id} | Name: {$p->name} | Cat: {$cat} | Parent: {$parent}\n";
}
