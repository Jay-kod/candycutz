<?php

namespace App\Providers;

use App\Domain\Gate\Services\DatabaseGateTracker;
use App\Domain\Notification\Services\ResendMailConfigurator;
use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Gateways\ManualTransferGateway;
use App\Domain\Payment\Gateways\PaystackGateway;
use App\Domain\Payment\Gateways\StripeGateway;
use App\Http\Responses\ApiResponse;
use App\Models\Address;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\RefundRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceCategoryRequest;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use App\Policies\AddressPolicy;
use App\Policies\AppointmentPolicy;
use App\Policies\BarberPolicy;
use App\Policies\BlogPostPolicy;
use App\Policies\GalleryPolicy;
use App\Policies\RefundRequestPolicy;
use App\Policies\ServiceCategoryPolicy;
use App\Policies\ServiceCategoryRequestPolicy;
use App\Policies\ServicePolicy;
use App\Policies\SettingPolicy;
use App\Policies\TestimonialPolicy;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DatabaseGateTracker::class);

        $this->app->bind(PaymentGateway::class, function ($app) {
            return match (config('payments.default')) {
                'paystack' => $app->make(PaystackGateway::class),
                'manual_transfer' => $app->make(ManualTransferGateway::class),
                'stripe' => $app->make(StripeGateway::class),
                default => $app->make(PaystackGateway::class),
            };
        });
    }

    public function boot(): void
    {
        // Domain Event -> Listener bindings are automatically discovered from app/Listeners by Laravel 12.
        // Boot Database Gate query tracking listener
        $this->app->make(DatabaseGateTracker::class)->bootListener();

        // Configure calibrated API Gate rate limiters
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by((string) $request->user()->id)
                : Limit::perMinute(60)->by((string) $request->ip());
        });

        RateLimiter::for('health-check', function (Request $request) {
            return Limit::perMinute(300)->by((string) $request->ip());
        });

        RateLimiter::for('auth-sensitive', function (Request $request) {
            $identity = (string) ($request->input('identity') ?? $request->input('email') ?? $request->input('username') ?? $request->ip());

            return [
                Limit::perMinute(10)
                    ->by($identity.'|'.$request->ip())
                    ->response(function (Request $request, array $headers) {
                        return ApiResponse::error('Too many requests. Please wait a moment before trying again.', [], 429, 'RATE_LIMIT_EXCEEDED')
                            ->withHeaders($headers);
                    }),
                Limit::perMinute(30)
                    ->by('auth-ip:'.$request->ip())
                    ->response(function (Request $request, array $headers) {
                        return ApiResponse::error('Too many authentication attempts from this IP address. Please wait a moment before trying again.', [], 429, 'RATE_LIMIT_EXCEEDED')
                            ->withHeaders($headers);
                    }),
            ];
        });

        Gate::define('manage-users', static fn (User $user): bool => ($user->role->value ?? $user->role) === 'super_admin');
        Gate::define('manage-barber-accounts', static fn (User $user): bool => in_array($user->role->value ?? $user->role, ['admin', 'super_admin'], true));
        Gate::define('manage-customers', static fn (User $user): bool => in_array($user->role->value ?? $user->role, ['admin', 'super_admin'], true));
        Gate::define('manage-settings', static fn (User $user): bool => in_array($user->role->value ?? $user->role, ['admin', 'super_admin'], true));

        Gate::policy(Address::class, AddressPolicy::class);
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(Barber::class, BarberPolicy::class);
        Gate::policy(BlogPost::class, BlogPostPolicy::class);
        Gate::policy(Gallery::class, GalleryPolicy::class);
        Gate::policy(RefundRequest::class, RefundRequestPolicy::class);
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(ServiceCategory::class, ServiceCategoryPolicy::class);
        Gate::policy(ServiceCategoryRequest::class, ServiceCategoryRequestPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(Testimonial::class, TestimonialPolicy::class);

        if (app()->environment('production') && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Model::preventLazyLoading(false);

        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            $cacheKey = "email_verification_otp:{$notifiable->id}";
            $cachedData = Cache::get($cacheKey);
            $otp = is_array($cachedData) ? ($cachedData['code'] ?? null) : $cachedData;

            if (! $otp || ! is_string($otp) || strlen($otp) !== 6) {
                $otp = sprintf('%06d', random_int(100000, 999999));
                Cache::put($cacheKey, [
                    'code' => $otp,
                    'email' => $notifiable->email,
                ], now()->addMinutes(15));
            }

            $name = $notifiable->name ?? 'Valued Client';

            $htmlContent = '
            <div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; max-width: 600px; margin: 0 auto; padding: 32px 24px; border: 1px solid #222; border-radius: 16px; background: #0c0e12; color: #f1f5f9;">
                <div style="margin-bottom: 24px; border-bottom: 1px solid #1e293b; padding-bottom: 20px;">
                    <span style="font-weight: 900; font-size: 24px; color: #ff6700; letter-spacing: -0.5px;">CandyCutz</span>
                    <span style="font-size: 11px; text-transform: uppercase; color: #94a3b8; margin-left: 10px; letter-spacing: 1.5px; font-weight: 600;">Luxury Grooming</span>
                </div>

                <h2 style="color: #ffffff; font-size: 20px; font-weight: 700; margin-top: 0; margin-bottom: 12px;">Verify Your Email Address</h2>

                <p style="font-size: 14px; color: #cbd5e1; line-height: 1.6; margin-bottom: 24px;">
                    Hello '.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').',<br><br>
                    Please verify your email address to secure your CandyCutz account. Once verified, this email is permanently bound to your account, protected against unauthorized duplicate registrations, and will receive all official appointment confirmations, receipts, and schedule updates.
                </p>

                <div style="text-align: center; margin: 32px 0; background: #161a22; border: 1px solid #2d3748; border-radius: 12px; padding: 24px 16px;">
                    <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; margin: 0 0 10px 0; font-weight: 600;">Your 6-Digit Verification Code</p>
                    <div style="display: inline-block; font-family: monospace, \'Courier New\', Courier; font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #ff6700; text-shadow: 0 0 15px rgba(255,103,0,0.3); -webkit-user-select: all; user-select: all;">'.htmlspecialchars((string) $otp, ENT_QUOTES, 'UTF-8').'</div>
                    <div style="margin-top: 14px;">
                        <span style="display: inline-block; font-size: 11px; color: #ff6700; background: rgba(255,103,0,0.12); border: 1px solid rgba(255,103,0,0.3); border-radius: 8px; padding: 6px 14px; font-weight: 700; -webkit-user-select: all; user-select: all;">
                            📋 Tap / Double-Click to Copy: '.htmlspecialchars((string) $otp, ENT_QUOTES, 'UTF-8').'
                        </span>
                    </div>
                    <p style="font-size: 11px; color: #64748b; margin: 10px 0 0 0;">Valid for 15 minutes</p>
                </div>

                <p style="font-size: 13px; color: #94a3b8; line-height: 1.6; text-align: center; margin-bottom: 20px;">
                    Alternatively, you can verify your account instantly by clicking the button below:
                </p>

                <div style="text-align: center; margin-bottom: 32px;">
                    <a href="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'" style="display: inline-block; background: linear-gradient(135deg, #ff6700 0%, #ea580c 100%); color: #ffffff; font-weight: 700; font-size: 14px; text-decoration: none; padding: 14px 32px; border-radius: 10px; box-shadow: 0 4px 14px rgba(255, 103, 0, 0.35);">
                        Verify Email Address
                    </a>
                </div>

                <hr style="border: none; border-top: 1px solid #1e293b; margin: 28px 0;" />

                <p style="font-size: 11px; color: #64748b; line-height: 1.5; margin: 0;">
                    If you did not create or update an account with CandyCutz, no further action is required and you may safely disregard this message.
                </p>
            </div>';

            return (new MailMessage)
                ->subject('CandyCutz — Verify Your Email Address ('.$otp.')')
                ->line(new HtmlString($htmlContent));
        });

        ResendMailConfigurator::hydrate();
    }
}
