<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Product extends Model
{
    
    use HasFactory;
    use Searchable;

    

    protected $fillable = [
        'vendor_id',
        'category_id',
        'brand_id',
        'name',
        'description',
        'price',
        'stock',
        'image',
        'status',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }



    public function toSearchableArray(){
    // specific fields you want to be searchable
       return [
            'id'=>$this->id,
           'name' => $this->name,
           'description' => $this->description,
           'price' => $this->price,
           'stock' => $this->stock, 
       ];
   }
}
