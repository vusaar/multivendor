<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$top_ids = [15, 12, 41, 17, 42, 43, 18, 13, 38, 16];
$products = Product::with(['category', 'category.parent'])->whereIn('id', $top_ids)->get();

foreach ($top_ids as $idx => $id) {
    $p = $products->firstWhere('id', $id);
    if ($p) {
        $parent = ($p->category && $p->category->parent) ? $p->category->parent->name : 'None';
        $cat = $p->category ? $p->category->name : 'None';
        echo ($idx + 1) . ". ID: {$id} | Name: {$p->name} | Cat: {$cat} | Parent: {$parent}\n";
    }
}
