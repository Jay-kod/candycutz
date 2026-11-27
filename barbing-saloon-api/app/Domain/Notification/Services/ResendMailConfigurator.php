<?php

declare(strict_types=1);

namespace App\Domain\Notification\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * Hydrates runtime Resend mailer and sender configuration from persisted settings.
 */
class ResendMailConfigurator
{
    /**
     * Apply saved Resend API key and sender defaults to the active mail manager.
     */
    public static function hydrate(bool $forceResendMailer = false): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        try {
            /** @var array<string, mixed> $settings */
            $settings = Cache::remember('mail:resend_runtime_config', 300, function (): array {
                return Setting::query()
                    ->whereIn('key', ['resend_api_key', 'mail_from', 'mail_from_name'])
                    ->pluck('value', 'key')
                    ->all();
            });

            $resendKey = (string) ($settings['resend_api_key'] ?? config('services.resend.key') ?? '');
            if ($resendKey !== '') {
                config([
                    'services.resend.key' => $resendKey,
                    'mail.mailers.resend.key' => $resendKey,
                ]);

                $defaultMailer = (string) config('mail.default');
                if ($forceResendMailer || in_array($defaultMailer, ['log', 'smtp', ''], true)) {
                    config(['mail.default' => 'resend']);
                }

                if (app()->bound('mail.manager')) {
                    Mail::purge('resend');
                }
            }

            if (! empty($settings['mail_from'])) {
                config(['mail.from.address' => (string) $settings['mail_from']]);
            }

            if (! empty($settings['mail_from_name'])) {
                config(['mail.from.name' => (string) $settings['mail_from_name']]);
            }
        } catch (\Throwable) {
            // Keep resilient if database connection or settings table is unavailable
        }
    }
}
