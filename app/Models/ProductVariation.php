<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ProductVariation extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id', 'sku', 'price', 'stock'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(
            VariationAttributeValue::class,
            'product_variation_attribute_value',
            'product_variation_id',
            'variation_attribute_value_id'
        );
    }

    public function variationImages()
    {
        return $this->hasMany(ProductVariationImage::class, 'product_variation_id');
    }
}
