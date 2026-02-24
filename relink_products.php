<?php

// Run this with: php artisan tinker < relink_products.php

use App\Models\Product;
use App\Models\MasterProduct;

// Keyword mapping for intelligent matching
$keywordMap = [
    'sneaker' => 'Sneaker',
    'shoe' => 'Shoe',
    'tshirt' => 'T-Shirt',
    't-shirt' => 'T-Shirt',
    'shirt' => 'Shirt',
    'blouse' => 'Shirt',
    'jeans' => 'Jeans',
    'trouser' => 'Trousers',
    'pant' => 'Trousers',
];

$products = Product::whereNull('master_product_id')->get();
$linked = 0;

foreach ($products as $product) {
    $masterProductName = null;
    $productNameLower = strtolower($product->name);
    
    // Try to match by keywords
    foreach ($keywordMap as $keyword => $masterName) {
        if (strpos($productNameLower, $keyword) !== false) {
            $masterProductName = $masterName;
            break;
        }
    }
    
    // If no keyword match, use the product name as-is
    if (!$masterProductName) {
        $masterProductName = $product->name;
    }
    
    $master = MasterProduct::firstOrCreate(['name' => $masterProductName]);
    $product->update(['master_product_id' => $master->id]);
    $linked++;
}

echo "Linked {$linked} products to master products.\n";
echo "Total products: " . Product::count() . "\n";
echo "Linked products: " . Product::whereNotNull('master_product_id')->count() . "\n";
echo "Unlinked products: " . Product::whereNull('master_product_id')->count() . "\n";
