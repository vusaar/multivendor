<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Brand;
use App\Models\VariationAttribute;
use App\Models\VariationAttributeValue;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class FashionSupportSeeder extends Seeder
{
    public function run()
    {
        try {
            // 1. Categories
            $hierarchy = [
                'Men' => [
                    'Apparel' => ['Shirts', 'T-shirts', 'Trousers', 'Jackets', 'Suits'],
                    'Footwear' => ['Sneakers', 'Formal Shoes', 'Boots', 'Sandals'],
                    'Accessories' => ['Belts', 'Watches', 'Wallets'],
                ],
                'Women' => [
                    'Apparel' => ['Tops', 'Dresses', 'Skirts', 'Blouses', 'Jeans'],
                    'Footwear' => ['Heels', 'Flats', 'Sneakers', 'Boots'],
                    'Accessories' => ['Handbags', 'Jewelry', 'Scarves'],
                ]
            ];

            foreach ($hierarchy as $gender => $types) {
                $genderCat = Category::firstOrCreate(['name' => $gender]);
                
                foreach ($types as $type => $items) {
                    $typeCat = Category::firstOrCreate(
                        ['name' => "$gender $type"],
                        ['parent_id' => $genderCat->id]
                    );
                    
                    foreach ($items as $item) {
                        Category::firstOrCreate(
                            ['name' => "$gender $item"],
                            ['parent_id' => $typeCat->id]
                        );
                    }
                }
            }

            // 2. Brands
            $brands = [
                'Nike' => 'Premium sportswear and footwear.',
                'Adidas' => 'Iconic athletic apparel and accessories.',
                'Zara' => 'Fast-fashion global leader.',
                'H&M' => 'Affordable and sustainable fashion.',
                'Levi\'s' => 'The world leader in denim.',
                'Gucci' => 'High-end luxury Italian fashion.',
                'Prada' => 'Sophisticated luxury and leather goods.',
                'Puma' => 'Forever Faster sportswear.',
                'Ralph Lauren' => 'Classic American style.',
                'Calvin Klein' => 'Modern and minimalist fashion.'
            ];

            foreach ($brands as $name => $desc) {
                Brand::firstOrCreate(['name' => $name], ['description' => $desc]);
            }

            // 3. Variations
            $variations = [
                'Size' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11', 'UK 12'],
                'Color' => ['White', 'Black', 'Navy', 'Grey', 'Red', 'Beige', 'Floral', 'Striped', 'Pastel Pink'],
                'Material' => ['Cotton', 'Polyester', 'Leather', 'Denim', 'Silk', 'Wool', 'Linen', 'Synthetic']
            ];

            foreach ($variations as $attrName => $values) {
                $attr = VariationAttribute::firstOrCreate(['name' => $attrName]);
                foreach ($values as $val) {
                    VariationAttributeValue::firstOrCreate(['variation_attribute_id' => $attr->id, 'value' => $val]);
                }
            }

            // 4. Vendors
            $admin = User::where('email', 'vusaar@gmail.com')->first() ?? User::first();
            if ($admin) {
                $customVendors = [
                    [
                        'shop_name' => 'The Fashion Hub',
                        'address' => '123 Fashion Ave',
                        'city' => 'Johannesburg',
                    ],
                    [
                        'shop_name' => 'Urban Trends',
                        'address' => '456 Trend Blvd',
                        'city' => 'Cape Town',
                    ],
                    [
                        'shop_name' => 'Elite Footwear',
                        'address' => '789 Sole St',
                        'city' => 'Durban',
                    ],
                ];

                foreach ($customVendors as $vData) {
                    Vendor::firstOrCreate(
                        ['shop_name' => $vData['shop_name']],
                        [
                            'user_id' => $admin->id,
                            'status' => 'approved',
                            'address' => $vData['address'],
                            'city' => $vData['city'],
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error("FashionSupportSeeder Error: " . $e->getMessage());
            throw $e;
        }
    }
}
