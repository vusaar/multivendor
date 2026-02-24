<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'source',
        'message',
    ];

    protected $casts = [
        'message' => 'array',
    ];

    /**
     * Helper to quickly log an entry.
     * 
     * @param string $level (info, error, warning, debug)
     * @param string $source (e.g., 'ProductSync', 'Auth')
     * @param mixed $message (string or array/object)
     */
    public static function log($level, $source, $message)
    {
        return self::create([
            'level' => $level,
            'source' => $source,
            'message' => is_array($message) || is_object($message) ? $message : ['text' => $message],
        ]);
    }
}
