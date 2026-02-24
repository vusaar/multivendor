<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;

$start = microtime(true);

$query = "ladies tops";

$sub = Product::join('vendors', 'products.vendor_id', '=', 'vendors.id')
    ->join('categories', 'products.category_id', '=', 'categories.id')
    ->leftJoin('product_variation_metadata', 'products.id', '=', 'product_variation_metadata.product_id')
    ->where('products.status', 'active')
    ->where(function($q) use ($query) {
        $q->whereRaw("similarity(products.name, ?) > 0.05", [$query])
          ->orWhereRaw("similarity(products.description, ?) > 0.05", [$query])
          ->orWhereRaw("similarity(product_variation_metadata.variation_search_text, ?) > 0.05", [$query]);
    })
    ->select('products.id', DB::raw("similarity(products.name, '$query') as score"))
    ->orderBy('score', 'desc')
    ->limit(5);

$results = $sub->get();

$end = microtime(true);
echo "Query took: " . ($end - $start) . " seconds\n";
echo "Results found: " . $results->count() . "\n";
