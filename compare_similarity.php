<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;

$query = "white tshirt";
echo "Query: '$query'\n\n";

$targetIds = [2, 8, 12, 50]; // T-Shirt, Sneaker, Shirt, T-Shirt
$products = Product::whereIn('id', $targetIds)->get();

$output = "Query: '$query'\n\n";

foreach ($products as $p) {
    try {
        $score = DB::selectOne("SELECT similarity(?, ?) as score", [$query, $p->search_context])->score;
        $matches = DB::selectOne("SELECT ? % ? as is_match", [$query, $p->search_context])->is_match;
        $output .= "ID: {$p->id} | Name: {$p->name} | Score: {$score} | % Match: " . ($matches ? 'YES' : 'NO') . "\n";
        $output .= "Context: {$p->search_context}\n\n";
    } catch (\Exception $e) {
        $output .= "Error for ID {$p->id}: " . $e->getMessage() . "\n";
    }
}

file_put_contents('similarity_results.txt', $output);
echo "Results written to similarity_results.txt\n";
