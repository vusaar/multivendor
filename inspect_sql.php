<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;

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
    ->select('products.id');

echo "SQL: " . $sub->toSql() . "\n";
echo "Bindings: " . json_encode($sub->getBindings()) . "\n";
