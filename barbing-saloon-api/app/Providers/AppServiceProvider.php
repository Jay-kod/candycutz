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
    }

    public function boot(): void
    {
        Gate::policy(Appointment::class, AppointmentPolicy::class);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Model::preventLazyLoading(! app()->isProduction());
    }
}