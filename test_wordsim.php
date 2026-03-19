<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;

$query = "white tshirt";
echo "Query: $query\n\n";

$ids = [2, 50, 8, 12];
foreach ($ids as $id) {
    $p = Product::find($id);
    $ws = DB::selectOne("SELECT word_similarity(?, ?) as s", [$query, $p->search_context])->s;
    $sim = DB::selectOne("SELECT similarity(?, ?) as s", [$query, $p->search_context])->s;
    echo "ID: $id | Name: {$p->name} | WordSim: $ws | Sim: $sim\n";
}
