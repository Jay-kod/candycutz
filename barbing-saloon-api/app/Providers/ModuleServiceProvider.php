<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $base = app_path('Modules');

        $modules = [
            'Auth' => ['api', 'throttle:10,1', 'sanitize.input', 'security.headers'],
            'Public' => ['api', 'throttle:60,1', 'sanitize.input', 'security.headers'],
            'Customer' => ['api', 'auth:sanctum', 'check.role:customer', 'throttle:120,1', 'sanitize.input', 'security.headers'],
            'Barber' => ['api', 'auth:sanctum', 'check.role:barber', 'throttle:120,1', 'sanitize.input', 'security.headers'],
            'Admin' => ['api', 'auth:sanctum', 'check.role:admin,super_admin', 'throttle:200,1', 'sanitize.input', 'security.headers'],
            'SuperAdmin' => ['api', 'auth:sanctum', 'check.role:super_admin', 'throttle:200,1', 'sanitize.input', 'security.headers'],
        ];

        foreach ($modules as $module => $middleware) {
            $routes = $base . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'routes.php';

            if (file_exists($routes)) {
                Route::middleware($middleware)->group($routes);
            }
        }
    }
}