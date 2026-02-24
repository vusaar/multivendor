<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'synonyms',
        'is_synced',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
