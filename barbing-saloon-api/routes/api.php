<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\HealthApiController;
use Illuminate\Support\Facades\Route;

// Canonical API v1 Routes (/api/v1/*)
Route::prefix('v1')
    ->middleware(['throttle:120,1', 'security.headers'])
    ->group(function () {
        Route::get('/health', [HealthApiController::class, 'health']);

        require base_path('routes/api/v1/auth.php');
        require base_path('routes/api/v1/catalogue.php');
        require base_path('routes/api/v1/booking.php');
        require base_path('routes/api/v1/payments.php');
        require base_path('routes/api/v1/account.php');
        require base_path('routes/api/v1/admin.php');
    });

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
