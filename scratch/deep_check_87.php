<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Category;

$p = Product::find(87);
if ($p) {
    echo "PRODUCT NAME: " . $p->name . "\n";
    echo "CATEGORY_ID: " . $p->category_id . "\n";
    
    $c = $p->category;
    if ($c) {
        echo "CATEGORY NAME: " . $c->name . "\n";
        echo "CATEGORY STATUS: " . ($c->status ?? 'N/A') . "\n";
        echo "CATEGORY PARENT_ID: " . ($c->parent_id ?? 'NULL') . "\n";
    } else {
        echo "CATEGORY RELATIONSHIP RETURNED NULL\n";
    }
} else {
    echo "PRODUCT 87 NOT FOUND\n";
}
