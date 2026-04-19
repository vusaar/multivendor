<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;

function printPath($id) {
    $c = Category::find($id);
    if (!$c) {
        echo "ID $id NOT FOUND\n";
        return;
    }
    echo "ID: $id | Name: " . $c->name . " | Status: " . ($c->status ?? 'NULL') . " | Parent: " . ($c->parent_id ?? 'ROOT') . "\n";
    if ($c->parent_id) {
        printPath($c->parent_id);
    }
}

echo "--- PATH FOR 107 ---\n";
printPath(107);
echo "\n--- PATH FOR 125 ---\n";
printPath(125);
