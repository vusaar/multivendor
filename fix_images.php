<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

$jsonPath = base_path('database/data/prepared_products.json');

if (!file_exists($jsonPath)) {
    die("Error: $jsonPath not found.\n");
}

$data = json_decode(file_get_contents($jsonPath), true);
$updatedCount = 0;
$downloadCount = 0;

// STEP 1: Update JSON
foreach ($data as &$product) {
    if (isset($product['image_url'])) {
        $originalUrl = $product['image_url'];
        
        $parsedUrl = parse_url($originalUrl);
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
            if (isset($queryParams['crop'])) {
                unset($queryParams['crop']);
                $newQuery = http_build_query($queryParams);
                $newUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'] . ($newQuery ? '?' . $newQuery : '');
                
                if ($newUrl !== $originalUrl) {
                    $product['image_url'] = $newUrl;
                    $updatedCount++;
                }
            }
        }
    }
}
unset($product); // break reference

file_put_contents($jsonPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Updated $updatedCount URLs in prepared_products.json\n";

// STEP 2: Download images
foreach ($data as $product) {
    if (isset($product['image_url'])) {
        $newUrl = $product['image_url'];
        $imageName = "photo_{$product['external_id']}.jpg";
        $storagePath = "products/{$imageName}";
        
        if (Storage::disk('public')->exists($storagePath)) {
            echo "Re-downloading image for {$product['name']} (ID: {$product['external_id']})...\n";
            $imageResponse = Http::get($newUrl);
            if ($imageResponse->successful()) {
                Storage::disk('public')->put($storagePath, $imageResponse->body());
                $downloadCount++;
            } else {
                echo "Failed to re-download image for {$product['name']}.\n";
            }
        }
    }
}

echo "Re-downloaded and overwrote $downloadCount images in storage.\n";
