<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\MasterProduct;

class RelinkProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:relink';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Relink all products to their master products using keyword matching';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
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

        $this->info("Found {$products->count()} products to link...");

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
            
            $this->line("  {$product->name} → {$masterProductName}");
        }

        $this->info("\nLinked {$linked} products to master products.");
        $this->info("Total products: " . Product::count());
        $this->info("Linked products: " . Product::whereNotNull('master_product_id')->count());
        $this->info("Unlinked products: " . Product::whereNull('master_product_id')->count());

        return Command::SUCCESS;
    }
}
