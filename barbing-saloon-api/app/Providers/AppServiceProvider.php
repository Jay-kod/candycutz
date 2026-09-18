<?php

namespace App\Providers;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Gateways\ManualTransferGateway;
use App\Domain\Payment\Gateways\PaystackGateway;
use App\Domain\Payment\Gateways\StripeGateway;
use App\Models\Appointment;
use App\Policies\AppointmentPolicy;
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

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Model::preventLazyLoading(false);
    }
}
