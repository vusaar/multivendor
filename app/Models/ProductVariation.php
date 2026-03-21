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

    protected $touches = ['product'];

    protected static function booted()
    {
        static::deleting(function ($variation) {
            foreach ($variation->variationImages as $image) {
                if (\Storage::disk('public')->exists($image->image_path)) {
                    \Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }
        });
    }

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
