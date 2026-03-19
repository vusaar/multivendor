<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

/**
 * Category Cleanup Script
 * Keeps: Fashion categories (from FashionSupportSeeder) + Categories in use by products
 */

echo "Starting Category Cleanup...\n";

// 1. Identify Fashion Roots and their descendants
$fashionRoots = ['Men', 'Women'];
$safeIds = [];

foreach ($fashionRoots as $rootName) {
    $root = Category::where('name', $rootName)->whereNull('parent_id')->first();
    if ($root) {
        $safeIds[] = $root->id;
        echo "Found Fashion Root: $rootName (ID: {$root->id})\n";
        
        // Add all descendants of this root
        $descendants = collect();
        $toProcess = [$root->id];
        
        while (!empty($toProcess)) {
            $currentId = array_shift($toProcess);
            $children = Category::where('parent_id', $currentId)->pluck('id')->toArray();
            foreach ($children as $childId) {
                if (!in_array($childId, $safeIds)) {
                    $safeIds[] = $childId;
                    $toProcess[] = $childId;
                }
            }
        }
    }
}

// 2. Identify Categories used by products
$usedCategoryIds = Product::distinct()->pluck('category_id')->filter()->toArray();
echo "Found " . count($usedCategoryIds) . " categories currently used by products.\n";

foreach ($usedCategoryIds as $id) {
    if (!in_array($id, $safeIds)) {
        $safeIds[] = $id;
        
        // Ensure all ancestors of the used category are also kept
        $current = Category::find($id);
        while ($current && $current->parent_id) {
            if (!in_array($current->parent_id, $safeIds)) {
                $safeIds[] = $current->parent_id;
                echo "Adding ancestor category: {$current->parent_id} (Parent of {$current->id})\n";
            }
            $current = Category::find($current->parent_id);
        }
    }
}

$safeIds = array_unique($safeIds);
echo "Total 'Safe' Categories to keep: " . count($safeIds) . "\n";

// 3. Perform Deletion
$totalBefore = Category::count();
$toDeleteCount = $totalBefore - count($safeIds);

if ($toDeleteCount <= 0) {
    echo "No categories need cleaning. All current categories are safe.\n";
    exit;
}

echo "Deleting $toDeleteCount categories...\n";

try {
    // We use a raw query or chunked deletion to avoid memory issues and handle foreign key constraints if they exist
    // However, since we are keeping used categories and their ancestors, FKs to products should be safe.
    // FKs within the categories table (parent_id) need to be handled by deleting from bottom up.
    
    $deletedCount = Category::whereNotIn('id', $safeIds)->delete();
    echo "Successfully deleted $deletedCount categories.\n";
    echo "Final Category Count: " . Category::count() . "\n";

} catch (\Exception $e) {
    echo "[ERROR] Cleanup failed: " . $e->getMessage() . "\n";
}
