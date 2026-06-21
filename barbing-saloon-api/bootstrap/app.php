<?php

return [
    'providers' => [
        App\Providers\AppServiceProvider::class,
        App\Providers\ModuleServiceProvider::class,
    ],
    'middleware' => [
        'aliases' => [
            'check.role' => App\Core\Http\Middleware\CheckRole::class,
            'sanitize.input' => App\Core\Http\Middleware\SanitizeInput::class,
            'force.https' => App\Core\Http\Middleware\ForceHttps::class,
            'security.headers' => App\Core\Http\Middleware\SecurityHeaders::class,
            'log.api.request' => App\Core\Http\Middleware\LogApiRequest::class,
        ],
    ],
];