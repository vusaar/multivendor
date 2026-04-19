<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$p = Product::find(87);
if ($p) {
    echo "PRODUCT NAME: " . $p->name . "\n";
    echo "VENDOR ID: " . ($p->vendor_id ?? 'NULL') . "\n";
    echo "VENDOR NAME: " . ($p->vendor ? $p->vendor->name : 'NULL') . "\n";
} else {
    echo "PRODUCT 87 NOT FOUND\n";
}
