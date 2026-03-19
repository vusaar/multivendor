<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$p = Product::with(['category', 'category.parent', 'category.parent.parent'])->find(14);
if ($p) {
    echo "Product: {$p->name}\n";
    $cat = $p->category;
    $path = [];
    while ($cat) {
        array_unshift($path, $cat->name);
        $cat = $cat->parent;
    }
    echo "Category Path: " . implode(" > ", $path) . "\n";
    echo "Search Context: {$p->search_context}\n";
} else {
    echo "Product 14 not found\n";
}
