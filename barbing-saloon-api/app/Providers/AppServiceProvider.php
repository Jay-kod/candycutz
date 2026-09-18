<?php

namespace App\Providers;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Gateways\ManualTransferGateway;
use App\Domain\Payment\Gateways\PaystackGateway;
use App\Domain\Payment\Gateways\StripeGateway;
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
use App\Policies\ServicePolicy;
use App\Policies\ServiceCategoryPolicy;
use App\Policies\TestimonialPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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
