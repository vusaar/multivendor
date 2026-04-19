<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$p = Product::latest()->first();
echo "PRODUCT NAME: " . $p->name . "\n";
echo "DESCRIPTION: " . $p->description . "\n";
echo "PRICE: " . $p->price . "\n";

$variation = $p->variations->first();
if ($variation) {
    echo "ATTRIBUTES:\n";
    foreach ($variation->attributeValues as $val) {
        echo "  - " . $val->attribute->name . ": " . $val->value . "\n";
    }
}
