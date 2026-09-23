<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'enabled_for',
        'rollout_percentage',
        'conditions',
        'is_active',
    ];

    protected $casts = [
        'enabled_for' => 'array',
        'conditions' => 'array',
        'rollout_percentage' => 'integer',
        'is_active' => 'boolean',
    ];

    public function isEnabledForPlatform(string $platform): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $enabledFor = $this->enabled_for ?? ['web', 'app'];

        return in_array(strtolower($platform), array_map('strtolower', $enabledFor), true);
    }
}
