<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$start = microtime(true);
$res = DB::select("SELECT id FROM products WHERE status = 'active' AND similarity(search_context, 'ladies tops') > 0.05 LIMIT 10");
$end = microtime(true);

echo "Raw SQL took: " . ($end - $start) . " seconds\n";
echo "Found: " . count($res) . "\n";
