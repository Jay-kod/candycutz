<?php

use App\Http\Controllers\Api\V1\BarberApiController;
use App\Http\Controllers\Api\V1\BlogApiController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('check.role:barber,admin,super_admin')->group(function () {
        // Gallery CRUD
        Route::post('/gallery', [GalleryApiController::class, 'store']);
        Route::put('/gallery/{id}', [GalleryApiController::class, 'update'])->whereNumber('id');
        Route::delete('/gallery/{id}', [GalleryApiController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware('check.role:admin,super_admin')->group(function () {
        // Services CRUD
        Route::post('/services', [ServiceApiController::class, 'store']);
        Route::put('/services/{id}', [ServiceApiController::class, 'update'])->whereNumber('id');
        Route::delete('/services/{id}', [ServiceApiController::class, 'destroy'])->whereNumber('id');

        // Service Categories CRUD
        Route::post('/service-categories', [ServiceApiController::class, 'storeCategory']);
        Route::put('/service-categories/{id}', [ServiceApiController::class, 'updateCategory'])->whereNumber('id');
        Route::delete('/service-categories/{id}', [ServiceApiController::class, 'destroyCategory'])->whereNumber('id');

        // Blog CRUD
        Route::post('/blog', [BlogApiController::class, 'store']);
        Route::put('/blog/{id}', [BlogApiController::class, 'update'])->whereNumber('id');
        Route::delete('/blog/{id}', [BlogApiController::class, 'destroy'])->whereNumber('id');
        Route::post('/blog/{id}/react', [BlogApiController::class, 'react'])->whereNumber('id');
        Route::delete('/blog/{id}/react', [BlogApiController::class, 'removeReaction'])->whereNumber('id');

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
