<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Canonical API v1 Routes (/api/v1/*)
Route::prefix('v1')
    ->middleware(['throttle:120,1', 'sanitize.input', 'security.headers'])
    ->group(base_path('routes/api_v1.php'));

// Canonical API Routes Alias (/api/*)
Route::middleware(['throttle:120,1', 'sanitize.input', 'security.headers'])
    ->group(base_path('routes/api_v1.php'));