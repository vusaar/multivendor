<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'query',
        'intent',
        'results',
        'results_count',
        'duration_ms'
    ];

    protected $casts = [
        'intent' => 'array',
        'results' => 'array'
    ];
}
