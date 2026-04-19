<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Brand;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\VariationAttribute;
use App\Models\VariationAttributeValue;
use App\Models\ProductVariation;
use App\Models\ProductVariationImage;

class UnsplashProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jsonPath = base_path('database/data/prepared_products.json');
        
        if (!file_exists($jsonPath)) {
            $this->command->error("Prepared products file not found at: {$jsonPath}");
            return;
        }

        $productsData = json_decode(file_get_contents($jsonPath), true);
        
        // Find a fallback vendor (ID 1)
        $vendor = Vendor::find(1) ?? Vendor::first();
        if (!$vendor) {
            $this->command->error("No vendor found in the system. Please create a vendor first.");
            return;
        }

        $this->command->info("Starting ingestion of " . count($productsData) . " products for vendor: {$vendor->shop_name}");

        foreach ($productsData as $data) {
            try {
                // 1. Handle Brand
                $brand = Brand::firstOrCreate(
                    ['name' => $data['brand']],
                    ['status' => 'approved']
                );

                // 2. Handle Image Download
                $unsplashId = $data['external_id'];
                $imageName = "photo_{$unsplashId}.jpg";
                $storagePath = "products/{$imageName}";

                if (!Storage::disk('public')->exists($storagePath)) {
                    $this->command->info("Downloading image for: {$data['name']}");
                    $imageResponse = Http::get($data['image_url']);
                    if ($imageResponse->successful()) {
                        Storage::disk('public')->put($storagePath, $imageResponse->body());
                    } else {
                        $this->command->warn("Failed to download image for: {$data['name']}. Using URL as fallback.");
                    }
                }

                // 2b. Check Idempotency (Skip if already seeded)
                $sku = strtoupper(substr($data['brand'], 0, 3)) . '-' . $unsplashId;
                if (ProductVariation::where('sku', $sku)->exists()) {
                    $this->command->info("Skipping already seeded product: {$data['name']}");
                    continue;
                }

                // 3. Create Product
                $product = Product::create([
                    'vendor_id' => $vendor->id,
                    'category_id' => $data['category_id'],
                    'brand_id' => $brand->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'stock' => rand(10, 50),
                    'status' => 'active',
                ]);

                // 4. Create Product Image
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $storagePath, // Relative path in public storage
                ]);

                // 5. Handle Attributes (Variations)
                // We create one default variation to hold the attributes
                $variation = ProductVariation::create([
                    'product_id' => $product->id,
                    'sku' => strtoupper(substr($data['brand'], 0, 3)) . '-' . $unsplashId,
                    'price' => $data['price'],
                    'stock' => rand(10, 50),
                ]);

                // 4b. Create Product Variation Image
                ProductVariationImage::create([
                    'product_variation_id' => $variation->id,
                    'image_path' => $storagePath,
                ]);

                if (isset($data['attributes']) && is_array($data['attributes'])) {
                    foreach ($data['attributes'] as $key => $value) {
                        if (is_array($value)) $value = implode(', ', $value);
                        
                        $keyLower = strtolower($key);
                        $attributeMapping = [
                            'color' => 'Color',
                            'material' => 'Material',
                            'fit' => 'Fit',
                            'pattern' => 'Pattern',
                            'size' => 'Size'
                        ];

                        if (isset($attributeMapping[$keyLower])) {
                            // Find the standard attribute
                            $attributeName = $attributeMapping[$keyLower];
                            $attribute = VariationAttribute::where('name', $attributeName)->first();

                            if ($attribute) {
                                // Find or create attribute value (e.g., Neon Green)
                                $attributeValue = VariationAttributeValue::firstOrCreate([
                                    'variation_attribute_id' => $attribute->id,
                                    'value' => $value,
                                ]);

                                // Link to variation
                                $variation->attributeValues()->syncWithoutDetaching([$attributeValue->id]);
                            }
                        } else {
                            // If it's a prominent feature (Style, Use Case, Features), append to description
                            $prominentKeys = ['style', 'use_case', 'features', 'condition'];
                            if (in_array($keyLower, $prominentKeys)) {
                                $product->description .= " | " . ucfirst($key) . ": " . $value;
                                $product->save();
                            }
                        }
                    }
                }

                $this->command->info("Succesfully seeded: {$product->name}");

            } catch (\Exception $e) {
                $this->command->error("Error seeding '{$data['name']}': " . $e->getMessage());
            }
        }

        $this->command->info("Seeding complete!");
    }
}
