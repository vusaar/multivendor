<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Api\StorefrontProductController;
use Illuminate\Http\Request;

$ctrl = new StorefrontProductController();
$query = "white tshirt";

$output = "Testing tryFastSearch for '$query'...\n";
$reflection = new ReflectionClass(StorefrontProductController::class);
$method = $reflection->getMethod('tryFastSearch');
$method->setAccessible(true);
$fastIds = $method->invoke($ctrl, $query);

$output .= "Fast Path IDs: " . (is_array($fastIds) ? implode(', ', $fastIds) : 'null/empty') . "\n";

$output .= "\nChecking similarity scores for ALL products matching 'white' or 'tshirt' via ILIKE...\n";
$candidates = App\Models\Product::where('name', 'ilike', '%white%')
    ->orWhere('name', 'ilike', '%tshirt%')
    ->orWhere('name', 'ilike', '%t-shirt%')
    ->get();

foreach ($candidates as $p) {
    if (!$p->search_context) continue;
    $score = App\Models\Product::selectRaw("similarity(search_context, ?) as s", [$query])->where('id', $p->id)->first()->s;
    $match = App\Models\Product::selectRaw("search_context % ? as m", [$query])->where('id', $p->id)->first()->m;
    $output .= "ID: {$p->id} | Name: {$p->name} | Score: $score | % Match: " . ($match ? 'YES' : 'NO') . "\n";
}

file_put_contents('fast_search_results.txt', $output);
echo "Results written to fast_search_results.txt\n";

echo "\nTesting full search() for '$query'...\n";
$request = new Request(['product' => $query]);
$response = $ctrl->search($request);
echo "Full search response data:\n";
print_r($response->getData());
