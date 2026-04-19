<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$p = Product::find(87);
if ($p) {
    echo "PRODUCT 87: " . $p->name . "\n";
    echo "CATEGORY ID: " . ($p->category_id ?? 'NULL') . "\n";
    echo "CATEGORY NAME: " . ($p->category ? $p->category->name : 'NULL') . "\n";
} else {
    echo "PRODUCT 87 NOT FOUND\n";
}
