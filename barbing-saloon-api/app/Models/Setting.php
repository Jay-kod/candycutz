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
        if (str_starts_with($key, 'page_hero_')) {
            return 'hero';
        }

        if (str_starts_with($key, 'hero_') || str_starts_with($key, 'cta_')) {
            return 'hero';
        }

        if (str_starts_with($key, 'about_')) {
            return 'about';
        }

        if (str_starts_with($key, 'features_')) {
            return 'features';
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

        if ($key === 'brevo_api_key' || str_starts_with($key, 'mail_')) {
            return 'notifications';
        }

        if (str_starts_with($key, 'theme_') || in_array($key, ['primary_color', 'accent_color'], true)) {
            return 'theme';
        }

        if (str_starts_with($key, 'splash_') || str_starts_with($key, 'onboarding_') || str_starts_with($key, 'login_') || str_starts_with($key, 'app_') || str_starts_with($key, 'mobile_')) {
            return 'mobile';
        }

        if (
            str_starts_with($key, 'terms_')
            || str_starts_with($key, 'privacy_')
            || str_starts_with($key, 'account_deletion_')
            || str_starts_with($key, 'legal_')
            || str_starts_with($key, 'security_')
            || str_starts_with($key, 'cookie_')
        ) {
            return 'legal';
        }

        if (str_starts_with($key, 'services_') || str_starts_with($key, 'gallery_')) {
            return 'content';
        }

        if (str_starts_with($key, 'animation_')) {
            return 'animation';
        }

        return 'general';
    }
}
