<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;

$categories = Category::with('parent')->limit(100)->get();
foreach ($categories as $c) {
    $path = $c->name;
    $p = $c->parent;
    $depth = 0;
    while ($p) {
        $path = $p->name . ' > ' . $path;
        $p = $p->parent;
        $depth++;
    }
    echo "ID: {$c->id} | Depth: {$depth} | Path: {$path}\n";
}
