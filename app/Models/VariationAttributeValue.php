<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariationAttributeValue extends Model
{
    use HasFactory;
    protected $fillable = ['variation_attribute_id', 'value'];

    public function attribute()
    {
        return $this->belongsTo(VariationAttribute::class, 'variation_attribute_id');
    }
}
