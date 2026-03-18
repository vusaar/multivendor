<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExternalProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $response = \Illuminate\Support\Facades\Http::get('https://dummyjson.com/products?limit=10');
        
        if ($response->successful()) {
            $products = $response->json()['products'];
            
            foreach ($products as $data) {
                try {
                    // Find a user for the vendor
                    $user = \App\Models\User::first() ?? \App\Models\User::factory()->create();

                    // Find or create default vendor
                    $vendor = \App\Models\Vendor::firstOrCreate(
                        ['shop_name' => 'Default Store'],
                        ['user_id' => $user->id, 'status' => 'approved']
                    );

                    // Find or create category
                    $category = \App\Models\Category::firstOrCreate(
                        ['name' => $data['category']],
                        ['status' => 'active']
                    );
                    
                    // Find or create brand
                    $brand = \App\Models\Brand::firstOrCreate(
                        ['name' => $data['brand'] ?? 'Generic'],
                        ['status' => 'active']
                    );
                    
                    // Create product using model to trigger Observer
                    $product = \App\Models\Product::create([
                        'vendor_id' => $vendor->id,
                        'category_id' => $category->id,
                        'brand_id' => $brand->id,
                        'name' => $data['title'],
                        'description' => $data['description'],
                        'price' => $data['price'],
                        'stock' => $data['stock'],
                        'status' => 'active',
                    ]);
                    
                    // Add additional images + thumbnail
                    $imageUrls = array_merge([$data['thumbnail']], $data['images'] ?? []);
                    $imageUrls = array_unique($imageUrls);

                    foreach ($imageUrls as $imageUrl) {
                        \App\Models\ProductImage::create([
                            'product_id' => $product->id,
                            'image' => $imageUrl,
                        ]);
                    }
                    
                    echo "Seeded: {$data['title']}\n";
                } catch (\Exception $e) {
                    echo "Error seeding '{$data['title']}': " . $e->getMessage() . "\n";
                }
            }
        } else {
            echo "Failed to fetch products from DummyJSON API.\n";
        }
    }
}
