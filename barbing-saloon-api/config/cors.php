<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_filter([
        'http://localhost:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
        'http://localhost:3000',
        'http://localhost:8081',
        'http://127.0.0.1:8081',
        'http://10.252.94.238:5174',
        'http://10.252.94.238:8081',
        env('FRONTEND_URL'),
        env('APP_URL'),
    ])),
    'allowed_origins_patterns' => [
        '#^https?://(localhost|127\.0\.0\.1|10\.\d{1,3}\.\d{1,3}\.\d{1,3}|192\.168\.\d{1,3}\.\d{1,3})(:\d+)?$#',
        '#^https?://.*\.candycutz\.ng$#',
        '#^https?://.*\.candycutz\.com$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['*'],
    'max_age' => 86400,
    'supports_credentials' => true,
];