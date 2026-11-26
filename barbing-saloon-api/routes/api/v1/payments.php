<?php

use App\Http\Controllers\Api\V1\PaymentApiController;
use App\Http\Controllers\Api\V1\PaymentWebhookApiController;
use App\Http\Controllers\Api\V1\ReceiptApiController;
use App\Http\Controllers\Api\V1\RefundApiController;
use Illuminate\Support\Facades\Route;

Route::get('/payments/return', [PaymentApiController::class, 'returnUrl']);
Route::get('/payments/webhook', [PaymentApiController::class, 'returnUrl']);
Route::post('/payments/webhook', [PaymentWebhookApiController::class, 'handle'])
    ->withoutMiddleware(['throttle:api']);
Route::get('/payments/checkout-data', [PaymentApiController::class, 'checkoutData']);
Route::post('/payments/confirm-transfer', [PaymentApiController::class, 'confirmTransfer']);
Route::get('/payments/receipts/signed/{appointmentId}', [ReceiptApiController::class, 'showSigned'])
    ->name('api.v1.payments.receipt.signed')
    ->whereNumber('appointmentId');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payments/checkout', [PaymentApiController::class, 'checkout']);
    Route::get('/payments/appointments/{appointmentId}/details', [PaymentApiController::class, 'paymentDetails'])->whereNumber('appointmentId');
    Route::post('/payments/appointments/{appointmentId}/receipt', [PaymentApiController::class, 'uploadReceipt'])->whereNumber('appointmentId');
    Route::get('/payments/appointments/{appointmentId}/receipt', [ReceiptApiController::class, 'show'])->whereNumber('appointmentId');
    Route::get('/payments/appointments/{appointmentId}/receipt-url', [ReceiptApiController::class, 'signedUrl'])->whereNumber('appointmentId');

    // Customer polling endpoint
    Route::get('/customer/payments/{reference}', [PaymentApiController::class, 'customerPaymentStatus']);

    // Barber collect payment at venue
    Route::post('/barber/appointments/{id}/collect-payment', [PaymentApiController::class, 'collectPaymentAtVenue'])->whereNumber('id');
    Route::post('/barber/bookings/{id}/collect-payment', [PaymentApiController::class, 'collectPaymentAtVenue'])->whereNumber('id');

    // Customer Refund Eligibility, Requests & Evidence
    Route::get('/appointments/{id}/refund-eligibility', [RefundApiController::class, 'eligibility'])->whereNumber('id');
    Route::get('/customer/refunds', [RefundApiController::class, 'myRefunds']);
    Route::post('/refunds', [RefundApiController::class, 'store']);
    Route::get('/refunds/{id}', [RefundApiController::class, 'show'])->whereNumber('id');
    Route::get('/refunds/{id}/evidence', [RefundApiController::class, 'evidence'])->whereNumber('id');
});
