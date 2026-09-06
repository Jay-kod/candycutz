<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'group'];

    protected $attributes = [
        'group' => 'general',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $setting) {
            if (empty($setting->group)) {
                $setting->group = self::resolveGroupForKey($setting->key ?? '');
            }
        });
    }

    public static function resolveGroupForKey(string $key): string
    {
        if (str_starts_with($key, 'hero_')) {
            return 'hero';
        }

        if (str_starts_with($key, 'contact_') || in_array($key, ['phone', 'email', 'address', 'city', 'state', 'latitude', 'longitude', 'map_link'], true)) {
            return 'contact';
        }

        if (str_starts_with($key, 'booking_') || in_array($key, ['deposit_amount', 'min_notice_hours', 'max_days_ahead', 'slot_interval_minutes'], true)) {
            return 'booking';
        }

        if (str_starts_with($key, 'social_') || in_array($key, ['instagram', 'facebook', 'whatsapp', 'twitter'], true)) {
            return 'social';
        }

        if (str_starts_with($key, 'meta_') || str_starts_with($key, 'seo_')) {
            return 'seo';
        }

        if (str_starts_with($key, 'theme_') || in_array($key, ['primary_color', 'accent_color'], true)) {
            return 'theme';
        }

        return 'general';
    }
}