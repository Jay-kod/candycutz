<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ConsentApiController;
use Illuminate\Support\Facades\Route;

// Cookie banner evidence is collected for guests and signed-in users alike.
Route::post('/consents/cookies', [ConsentApiController::class, 'storeCookieConsent'])
    ->middleware('throttle:30,1');
