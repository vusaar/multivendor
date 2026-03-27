<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = ['products', 'vendors', 'search_logs'];

foreach ($tables as $table) {
    echo "\n=== Columns for Table: $table ===\n";
    try {
        $columns = Schema::getColumnListing($table);
        foreach ($columns as $column) {
            echo "- $column\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
