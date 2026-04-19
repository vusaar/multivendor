<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$p = Product::orderBy('id', 'desc')->first(); // Get the most recent one (from our new batch)
if ($p) {
    echo "LATEST PRODUCT: " . $p->name . " (ID: " . $p->id . ")\n";
    echo "CATEGORY: " . ($p->category ? $p->category->name : 'MISSING') . " (Status: " . ($p->category->status ?? 'N/A') . ")\n";
    
    $v = $p->variations()->first();
    if ($v) {
        echo "VARIATION SKU: " . $v->sku . "\n";
        $vi = $v->variationImages()->first();
        echo "VARIATION IMAGE: " . ($vi ? $vi->image_path : 'MISSING') . "\n";
    } else {
        echo "VARIATION MISSING\n";
    }
}
