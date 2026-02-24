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
        'master_product_id',
    ];

    public function masterProduct()
    {
        return $this->belongsTo(MasterProduct::class);
    }

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
           'categories' => [$this->category ? $this->category->name : null,
                           $this->category ? ($this->category->parent ? $this->category->parent->name : null) : null,
                           $this->category ? ($this->category->parent && $this->category->parent->parent ? $this->category->parent->parent->name : null) : null],
           'vendor' => $this->vendor ? $this->vendor->shop_name : null,
           'brand'=> $this->brand ? $this->brand->name : null,
           'variations' => $this->variations->reduce(function ($attributes,$variation){


                $arr1 = $variation->attributeValues->reduce(function($arr2,$val){

                     $attr_name_arr = [];

                    if(key_exists($val->attribute->name,$arr2)){
                        array_push($arr2[$val->attribute->name], $val->value);
                       // array_push($attr_name_arr,$val->value);
                        
                    }else{
                        $arr2[$val->attribute->name] = [$val->value];
                    }
                     
                    return $arr2;

                },[]);

                //  return array_merge($variation->attributeValues->pluck('value')->toArray(),$attributes);
                 return array_merge($arr1,$attributes);
           },[]),

            'variation_string' => $this->variations->reduce(function ($carry, $variation) {
                $values = $variation->attributeValues->pluck('value')->toArray();
                return $carry . ' ' . implode(' ', $values);
            }, ''),
            'synonyms' => $this->masterProduct ? $this->masterProduct->synonyms : null,
       ];
    }
}
