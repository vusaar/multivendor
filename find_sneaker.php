<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Category;

$p = Product::with('category')->find(59);
if ($p && $p->category) {
    echo "Product: " . $p->name . "\n";
    echo "Category: " . $p->category->name . "\n";
    
    $parent = $p->category->parent;
    $path = [$p->category->name];
    while ($parent) {
        $path[] = $parent->name;
        $parent = $parent->parent;
    }
    echo "Category Path: " . implode(" > ", array_reverse($path)) . "\n";
} else {
    echo "Product or Category not found.\n";
}

echo "\nListing all categories containing 'Sport' or 'Shoe':\n";
$cats = Category::where('name', 'ILIKE', '%sport%')->orWhere('name', 'ILIKE', '%shoe%')->get();
foreach ($cats as $cat) {
    echo "- " . $cat->name . " (ID: " . $cat->id . ")\n";
}
