<?php

use App\Http\Controllers\Api\V1\PaymentApiController;
use App\Http\Controllers\Api\V1\PaymentWebhookApiController;
use App\Http\Controllers\Api\V1\ReceiptApiController;
use Illuminate\Support\Facades\Route;

Route::get('/payments/return', [PaymentApiController::class, 'returnUrl']);
Route::post('/payments/webhook', [PaymentWebhookApiController::class, 'handle']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payments/checkout', [PaymentApiController::class, 'checkout']);
    Route::get('/payments/appointments/{appointmentId}/details', [PaymentApiController::class, 'paymentDetails'])->whereNumber('appointmentId');
    Route::post('/payments/appointments/{appointmentId}/receipt', [PaymentApiController::class, 'uploadReceipt'])->whereNumber('appointmentId');
    Route::get('/payments/appointments/{appointmentId}/receipt', [ReceiptApiController::class, 'show'])->whereNumber('appointmentId');

    // Customer polling endpoint
    Route::get('/customer/payments/{reference}', [PaymentApiController::class, 'customerPaymentStatus']);

    // Barber collect payment at venue
    Route::post('/barber/appointments/{id}/collect-payment', [PaymentApiController::class, 'collectPaymentAtVenue'])->whereNumber('id');
    Route::post('/barber/bookings/{id}/collect-payment', [PaymentApiController::class, 'collectPaymentAtVenue'])->whereNumber('id');
});
