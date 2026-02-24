<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'T-Shirt' => 'Tshirt, Top, Tee',
            'Shirt' => 'Top, Blouse, Tunic',
            'Shoe' => 'Footwear',
            'Sneaker' => 'Trainer',
            'Jeans' => 'Denim',
            'Trousers' => 'Pants',
        ];

        foreach ($data as $name => $synonyms) {
            $master = \App\Models\MasterProduct::updateOrCreate(
                ['name' => $name],
                ['synonyms' => $synonyms]
            );

            // Link existing products with this name to this master product
            \App\Models\Product::where('name', $name)
                ->whereNull('master_product_id')
                ->update(['master_product_id' => $master->id]);
        }

        // Handle any left-over products by creating master products for them
        $remainingProducts = \App\Models\Product::whereNull('master_product_id')->get();
        
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
        
        foreach ($remainingProducts as $product) {
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
            
            $master = \App\Models\MasterProduct::firstOrCreate(
                ['name' => $masterProductName]
            );
            $product->update(['master_product_id' => $master->id]);
        }
    }
}
