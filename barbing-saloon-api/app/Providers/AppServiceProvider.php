<?php

namespace App\Providers;

use App\Domain\Gate\Services\DatabaseGateTracker;
use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Gateways\ManualTransferGateway;
use App\Domain\Payment\Gateways\PaystackGateway;
use App\Domain\Payment\Gateways\StripeGateway;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use App\Policies\AppointmentPolicy;
use App\Policies\BarberPolicy;
use App\Policies\BlogPostPolicy;
use App\Policies\GalleryPolicy;
use App\Policies\ServiceCategoryPolicy;
use App\Policies\ServicePolicy;
use App\Policies\TestimonialPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        // Boot Database Gate query tracking listener
        $this->app->make(DatabaseGateTracker::class)->bootListener();

        // Configure calibrated API Gate rate limiters
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by((string) $request->user()->id)
                : Limit::perMinute(60)->by((string) $request->ip());
        });

        RateLimiter::for('auth-sensitive', function (Request $request) {
            $identity = (string) ($request->input('identity') ?? $request->input('email') ?? $request->input('username') ?? $request->ip());

            return Limit::perMinute(10)
                ->by($identity.'|'.$request->ip())
                ->response(function () {
                    return ApiResponse::error('Too many requests. Please wait a moment before trying again.', [], 429, 'RATE_LIMIT_EXCEEDED');
                });
        });

        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(Barber::class, BarberPolicy::class);
        Gate::policy(BlogPost::class, BlogPostPolicy::class);
        Gate::policy(Gallery::class, GalleryPolicy::class);
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(ServiceCategory::class, ServiceCategoryPolicy::class);
        Gate::policy(Testimonial::class, TestimonialPolicy::class);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Model::preventLazyLoading(false);
    }
}
