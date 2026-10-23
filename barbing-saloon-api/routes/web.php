<?php

use App\Http\Controllers\Api\V1\PaymentApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response('Barbing Saloon API'));
Route::get('/pay/return', [PaymentApiController::class, 'returnUrl']);
