<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Canonical API v1 Routes (/api/v1/*)
Route::prefix('v1')
    ->middleware(['throttle:120,1', 'security.headers'])
    ->group(base_path('routes/api_v1.php'));

// Canonical API Routes Alias (/api/*)
Route::middleware(['throttle:120,1', 'security.headers'])
    ->group(base_path('routes/api_v1.php'));

// Legacy 410 Gone Fallbacks
Route::any('/customer/{any?}', function () {
    return response()->json(['error' => 'This legacy endpoint has been permanently removed. Please upgrade to the v1 API.'], 410);
})->where('any', '.*');

Route::any('/barber/{any?}', function () {
    return response()->json(['error' => 'This legacy endpoint has been permanently removed. Please upgrade to the v1 API.'], 410);
})->where('any', '.*');

Route::any('/admin/{any?}', function () {
    return response()->json(['error' => 'This legacy endpoint has been permanently removed. Please upgrade to the v1 API.'], 410);
})->where('any', '.*');

Route::any('/super_admin/{any?}', function () {
    return response()->json(['error' => 'This legacy endpoint has been permanently removed. Please upgrade to the v1 API.'], 410);
})->where('any', '.*');
