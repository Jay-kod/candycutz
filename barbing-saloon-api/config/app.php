<?php

return [
    'name' => env('APP_NAME', 'Barbing Saloon'),
    'env' => env('APP_ENV', 'local'),
    'url' => env('APP_URL', 'http://localhost:8000'),
    'providers' => [
        App\Providers\AppServiceProvider::class,
        App\Providers\ModuleServiceProvider::class,
    ],
];