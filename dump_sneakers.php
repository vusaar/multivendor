<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$products = Product::where('name', 'ilike', '%sneaker%')->get(['id', 'name', 'description', 'search_context']);
file_put_contents('sneaker_products.json', $products->toJson(JSON_PRETTY_PRINT));
echo "Dumped " . $products->count() . " products to sneaker_products.json\n";
