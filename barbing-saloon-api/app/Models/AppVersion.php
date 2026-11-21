<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'platform',
        'is_minimum',
        'is_latest',
        'force_update',
        'release_notes',
        'store_url',
        'released_at',
    ];

    protected $casts = [
        'is_minimum' => 'boolean',
        'is_latest' => 'boolean',
        'force_update' => 'boolean',
        'released_at' => 'datetime',
    ];

    public function scopeForPlatform($query, string $platform)
    {
        return $query->where('platform', strtolower($platform));
    }

    public function scopeMinimum($query)
    {
        return $query->where('is_minimum', true);
    }

    public function scopeLatestVersion($query)
    {
        return $query->where('is_latest', true);
    }
}
