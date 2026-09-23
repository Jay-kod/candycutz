<?php

use App\Http\Controllers\Api\V1\BarberApiController;
use App\Http\Controllers\Api\V1\BlogApiController;
use App\Http\Controllers\Api\V1\AppTelemetryApiController;
use App\Http\Controllers\Api\V1\FeatureFlagApiController;
use App\Http\Controllers\Api\V1\GalleryApiController;
use App\Http\Controllers\Api\V1\PublicApiController;
use App\Http\Controllers\Api\V1\ServiceApiController;
use App\Http\Controllers\Api\V1\ServiceZoneApiController;
use App\Http\Controllers\Api\V1\TestimonialApiController;
use Illuminate\Support\Facades\Route;

// Public Endpoints
Route::get('/settings', [PublicApiController::class, 'settings']);
Route::get('/services', [ServiceApiController::class, 'index']);
Route::get('/service-categories', [ServiceApiController::class, 'categories']);
Route::get('/services/{idOrSlug}', [ServiceApiController::class, 'show']);
Route::get('/barbers', [BarberApiController::class, 'index']);
Route::get('/barbers/{id}', [BarberApiController::class, 'show'])->whereNumber('id');
Route::get('/service-zones', [ServiceZoneApiController::class, 'index']);
Route::get('/gallery', [GalleryApiController::class, 'index']);
Route::get('/gallery/{id}', [GalleryApiController::class, 'show'])->whereNumber('id');
Route::get('/testimonials', [TestimonialApiController::class, 'index']);
Route::get('/blog', [BlogApiController::class, 'index']);
Route::get('/blog/{slug}', [BlogApiController::class, 'show']);
Route::get('/working-hours', [PublicApiController::class, 'workingHours']);
Route::get('/available-slots', [PublicApiController::class, 'availableSlots']);
Route::post('/contact', [PublicApiController::class, 'contact']);
Route::get('/feature-flags', [FeatureFlagApiController::class, 'publicIndex']);
Route::post('/app/crashes', [AppTelemetryApiController::class, 'reportCrash']);

// Public Aliases (/api/v1/public/*) for seamless backward compatibility
Route::prefix('public')->group(function () {
    Route::get('/settings', [PublicApiController::class, 'settings']);
    Route::get('/services', [ServiceApiController::class, 'index']);
    Route::get('/service-categories', [ServiceApiController::class, 'categories']);
    Route::get('/services/{idOrSlug}', [ServiceApiController::class, 'show']);
    Route::get('/barbers', [BarberApiController::class, 'index']);
    Route::get('/barbers/{id}', [BarberApiController::class, 'show'])->whereNumber('id');
    Route::get('/service-zones', [ServiceZoneApiController::class, 'index']);
    Route::get('/gallery', [GalleryApiController::class, 'index']);
    Route::get('/gallery/{id}', [GalleryApiController::class, 'show'])->whereNumber('id');
    Route::get('/testimonials', [TestimonialApiController::class, 'index']);
    Route::get('/blog', [BlogApiController::class, 'index']);
    Route::get('/blog/{slug}', [BlogApiController::class, 'show']);
    Route::get('/working-hours', [PublicApiController::class, 'workingHours']);
    Route::get('/available-slots', [PublicApiController::class, 'availableSlots']);
    Route::post('/contact', [PublicApiController::class, 'contact']);
    Route::get('/feature-flags', [FeatureFlagApiController::class, 'publicIndex']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('check.role:barber,admin,super_admin')->group(function () {
        // Gallery CRUD
        Route::post('/gallery', [GalleryApiController::class, 'store']);
        Route::put('/gallery/{id}', [GalleryApiController::class, 'update'])->whereNumber('id');
        Route::delete('/gallery/{id}', [GalleryApiController::class, 'destroy'])->whereNumber('id');

        // Services CRUD (barbers submit pending services; admin directly creates)
        Route::post('/services', [ServiceApiController::class, 'store']);
        Route::put('/services/{id}', [ServiceApiController::class, 'update'])->whereNumber('id');
        Route::delete('/services/{id}', [ServiceApiController::class, 'destroy'])->whereNumber('id');

        // Blog CRUD (barbers and admin can create/edit their blog posts)
        Route::post('/blog', [BlogApiController::class, 'store']);
        Route::put('/blog/{id}', [BlogApiController::class, 'update'])->whereNumber('id');
        Route::delete('/blog/{id}', [BlogApiController::class, 'destroy'])->whereNumber('id');
    });

    // Customer review / testimonial submission (any authenticated customer)
    Route::post('/testimonials', [TestimonialApiController::class, 'store']);
    Route::get('/reviews/me', [TestimonialApiController::class, 'customerReviews']);

    // Blog reactions (any authenticated user)
    Route::post('/blog/{id}/react', [BlogApiController::class, 'react'])->whereNumber('id');
    Route::delete('/blog/{id}/react', [BlogApiController::class, 'removeReaction'])->whereNumber('id');

    Route::middleware('check.role:admin,super_admin')->group(function () {
        // Services Approval / Rejection
        Route::patch('/services/{id}/approve', [ServiceApiController::class, 'approve'])->whereNumber('id');
        Route::patch('/services/{id}/reject', [ServiceApiController::class, 'reject'])->whereNumber('id');

        // Service Categories CRUD
        Route::post('/service-categories', [ServiceApiController::class, 'storeCategory']);
        Route::put('/service-categories/{id}', [ServiceApiController::class, 'updateCategory'])->whereNumber('id');
        Route::delete('/service-categories/{id}', [ServiceApiController::class, 'destroyCategory'])->whereNumber('id');

        // Testimonials CRUD
        Route::put('/testimonials/{id}', [TestimonialApiController::class, 'update'])->whereNumber('id');
        Route::patch('/testimonials/{id}/approve', [TestimonialApiController::class, 'approve'])->whereNumber('id');
        Route::patch('/testimonials/{id}/feature', [TestimonialApiController::class, 'feature'])->whereNumber('id');
        Route::delete('/testimonials/{id}', [TestimonialApiController::class, 'destroy'])->whereNumber('id');

        // Barber Management
        Route::post('/barbers', [BarberApiController::class, 'store']);
        Route::put('/barbers/{id}', [BarberApiController::class, 'update'])->whereNumber('id');
        Route::patch('/barbers/{id}/status', [BarberApiController::class, 'updateStatus'])->whereNumber('id');
        Route::delete('/barbers/{id}', [BarberApiController::class, 'destroy'])->whereNumber('id');
    });
});
