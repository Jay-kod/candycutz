<?php

namespace App\Providers;

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
        $this->app->bind(\App\Domain\Payment\Contracts\PaymentGateway::class, function ($app) {
            return match (config('payments.default')) {
                'paystack' => $app->make(\App\Domain\Payment\Gateways\PaystackGateway::class),
                'manual_transfer' => $app->make(\App\Domain\Payment\Gateways\ManualTransferGateway::class),
                default => $app->make(\App\Domain\Payment\Gateways\PaystackGateway::class),
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
