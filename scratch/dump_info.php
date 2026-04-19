<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Vendor;
use App\Models\Category;

$vendor = Vendor::first();
$category = Category::first();

echo "VENDOR: " . json_encode($vendor) . "\n";
echo "CATEGORY: " . json_encode($category) . "\n";
