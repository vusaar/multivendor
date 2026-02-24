<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\StorefrontProductController;
use Illuminate\Http\Request;

$controller = new StorefrontProductController();
$request = new Request(['product' => 'ladies tops for less than $10']);

$start = microtime(true);
$response = $controller->search($request);
$end = microtime(true);

echo "Search took: " . ($end - $start) . " seconds\n";
echo "Response Status: " . $response->getStatusCode() . "\n";
echo "Fast Path used? Check logs or code logic success.\n";

$data = json_decode($response->getContent(), true);
echo "Results count: " . count($data['data'] ?? $data) . "\n";
if (isset($data['data'])) {
    foreach($data['data'] as $p) {
        echo "- {$p['name']} (\${$p['price']})\n";
    }
}
