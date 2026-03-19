<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Category;

$p = Product::find(14);
echo "ProductID: 14 | CatID: {$p->category_id}\n";
$c = Category::find($p->category_id);
if ($c) {
    echo "Category: {$c->name} (ID: {$c->id}, Parent: {$c->parent_id})\n";
    $p1 = Category::find($c->parent_id);
    if ($p1) {
        echo "Parent: {$p1->name} (ID: {$p1->id}, Parent: {$p1->parent_id})\n";
        $p2 = Category::find($p1->parent_id);
        if ($p2) {
             echo "Grandparent: {$p2->name} (ID: {$p2->id})\n";
        }
    }
}
