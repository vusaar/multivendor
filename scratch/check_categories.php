<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;

$c107 = Category::find(107);
$c106 = Category::find(106);
$c125 = Category::find(125);
$c124 = Category::find(124);

echo "MEN SNEAKERS (107): Status=" . ($c107->status ?? 'NULL') . ", Parent=" . ($c107->parent_id ?? 'NULL') . "\n";
echo "MEN FOOTWEAR (106): Status=" . ($c106->status ?? 'NULL') . ", Parent=" . ($c106->parent_id ?? 'NULL') . "\n";
echo "WOMEN SNEAKERS (125): Status=" . ($c125->status ?? 'NULL') . ", Parent=" . ($c125->parent_id ?? 'NULL') . "\n";
echo "WOMEN FOOTWEAR (124): Status=" . ($c124->status ?? 'NULL') . ", Parent=" . ($c124->parent_id ?? 'NULL') . "\n";
