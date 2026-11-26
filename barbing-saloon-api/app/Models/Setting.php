<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

        static::saved(function (self $setting) {
            if (! empty($setting->group)) {
                Cache::forget("settings.group.{$setting->group}");
            }
            Cache::forget('settings.public');
            Cache::forget('settings.all');
            Cache::forget('paystack_gateway_credentials');
            Cache::forget('salon_clock:shop_timezone');
            Cache::forget('mail:resend_runtime_config');
            Cache::forget('appointment_resource:bank_settings');
            Cache::forget('policies.no_show');
            Cache::forget('policies.refund');
            Cache::forget('catalogue.shop_location');
            Cache::forget("setting.{$setting->key}");
        });

        static::deleted(function (self $setting) {
            if (! empty($setting->group)) {
                Cache::forget("settings.group.{$setting->group}");
            }
            Cache::forget('settings.public');
            Cache::forget('settings.all');
            Cache::forget('paystack_gateway_credentials');
            Cache::forget('salon_clock:shop_timezone');
            Cache::forget('mail:resend_runtime_config');
            Cache::forget('appointment_resource:bank_settings');
            Cache::forget('policies.no_show');
            Cache::forget('policies.refund');
            Cache::forget('catalogue.shop_location');
            Cache::forget("setting.{$setting->key}");
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

        if (str_starts_with($key, 'bank_') || str_starts_with($key, 'manual_transfer_') || str_starts_with($key, 'paystack_') || str_starts_with($key, 'payment_') || in_array($key, ['bank_name', 'account_name', 'account_number'], true)) {
            return 'payment';
        }

        if (str_starts_with($key, 'booking_') || str_starts_with($key, 'no_show_') || str_starts_with($key, 'grace_') || str_starts_with($key, 'refund_') || in_array($key, ['deposit_amount', 'min_notice_hours', 'max_days_ahead', 'slot_interval_minutes', 'cancellation_cutoff_hours'], true)) {
            return 'booking';
        }

        if (str_starts_with($key, 'social_') || in_array($key, ['instagram', 'facebook', 'whatsapp', 'twitter'], true)) {
            return 'social';
        }

        if (in_array($key, ['google_auth_enabled', 'google_client_id', 'google_client_ids', 'apple_client_id'], true)) {
            return 'social';
        }

        if (str_starts_with($key, 'meta_') || str_starts_with($key, 'seo_')) {
            return 'seo';
        }

        if ($key === 'brevo_api_key' || $key === 'resend_api_key' || str_starts_with($key, 'mail_')) {
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

        if (str_starts_with($key, 'services_') || str_starts_with($key, 'gallery_') || str_starts_with($key, 'testimonials_')) {
            return 'content';
        }

        if (str_starts_with($key, 'animation_')) {
            return 'animation';
        }

        return 'general';
    }
}
