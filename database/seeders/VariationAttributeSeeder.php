<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VariationAttribute;

class VariationAttributeSeeder extends Seeder
{
    public function run()
    {
        $attributes = [
            'Color',
            'Size',
            'Material',
            'Fit',
            'Pattern',
            'Sleeve Length',
            'Neckline',
        ];
        foreach ($attributes as $attr) {
            VariationAttribute::updateOrCreate(['name' => $attr], ['name' => $attr]);
        }
    }
}
