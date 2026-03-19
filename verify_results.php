<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$ids = [12,13,15,17,18,14,38,16];
$products = Product::with(['category', 'category.parent'])->whereIn('id', $ids)->get();

foreach ($products as $p) {
    $parent = ($p->category && $p->category->parent) ? $p->category->parent->name : 'None';
    $cat = $p->category ? $p->category->name : 'None';
    echo "ID: {$p->id} | Name: {$p->name} | Cat: {$cat} | Parent: {$parent}\n";
}
