<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;

$query = "white tshirt";
echo "Trace for Query: '$query'\n\n";

$uids = [2, 8, 12, 50];
foreach ($uids as $id) {
    $p = Product::find($id);
    if (!$p) continue;
    
    // Use raw SELECT to be safe with pg_trgm
    $sim = DB::select("SELECT similarity(?, ?) as s", [$query, $p->search_context])[0]->s;
    $match = DB::select("SELECT (? % ?) as m", [$p->search_context, $query])[0]->m;
    
    echo "ID: $id | Name: {$p->name}\n";
    echo "  Similarity: " . number_format($sim, 4) . "\n";
    echo "  Matches Threshold (%): " . ($match ? "YES" : "NO") . "\n";
    echo "  Context: " . substr(str_replace("\n", " ", $p->search_context), 0, 80) . "...\n\n";
}

$threshold = DB::select("SHOW pg_trgm.similarity_threshold")[0]->similarity_threshold;
echo "Current PG Threshold: $threshold\n";
