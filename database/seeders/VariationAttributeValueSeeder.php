<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VariationAttribute;
use App\Models\VariationAttributeValue;

class VariationAttributeValueSeeder extends Seeder
{
    public function run()
    {
        $values = [
            'Color' => ['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow', 'Brown', 'Grey', 'Pink', 'Purple'],
            'Size' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL'],
            'Material' => ['Cotton', 'Polyester', 'Wool', 'Silk', 'Denim', 'Leather', 'Linen'],
            'Fit' => ['Slim', 'Regular', 'Loose', 'Oversized'],
            'Pattern' => ['Solid', 'Striped', 'Checked', 'Printed', 'Floral', 'Polka Dot'],
            'Sleeve Length' => ['Short Sleeve', 'Long Sleeve', 'Sleeveless', '3/4 Sleeve'],
            'Neckline' => ['Round', 'V-Neck', 'Collared', 'Boat Neck', 'Turtleneck'],
        ];
        foreach ($values as $attrName => $attrValues) {
            $attribute = VariationAttribute::where('name', $attrName)->first();
            if ($attribute) {
                foreach ($attrValues as $val) {
                    VariationAttributeValue::updateOrCreate([
                        'variation_attribute_id' => $attribute->id,
                        'value' => $val
                    ], [
                        'variation_attribute_id' => $attribute->id,
                        'value' => $val
                    ]);
                }
            }
        }
    }
}
