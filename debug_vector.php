<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$id = 2; // Test with ID 2 which failed in previous run
echo "Inspecting Product ID $id\n";

$p = DB::table('products')->where('id', $id)->first();
echo "Current Context: " . ($p->search_context ?: 'NULL') . "\n";
echo "Current Embedding (Raw): " . ($p->embedding ? 'PRESENT' : 'NULL') . "\n";

// Try manual update with small fake vector
echo "Testing manual update with fake 1536 vector...\n";
$vec = '[' . implode(',', array_fill(0, 1536, 0.123)) . ']';
$affected = DB::update("UPDATE products SET embedding = ?::vector WHERE id = ?", [$vec, $id]);
echo "Affected: $affected\n";

$p2 = DB::table('products')->where('id', $id)->first();
echo "After Update Embedding (Raw): " . ($p2->embedding ? 'PRESENT' : 'NULL') . "\n";

// If STILL null, check for triggers or check constraints
$constraints = DB::select("SELECT conname FROM pg_constraint WHERE conrelid = 'products'::regclass");
print_r($constraints);
