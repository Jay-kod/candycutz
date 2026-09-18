<?php

use App\Http\Controllers\Api\V1\PaymentApiController;
use App\Http\Controllers\Api\V1\PaymentWebhookApiController;
use App\Http\Controllers\Api\V1\ReceiptApiController;
use Illuminate\Support\Facades\Route;

Route::post('/payments/webhook', [PaymentWebhookApiController::class, 'handle']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payments/checkout', [PaymentApiController::class, 'checkout']);
    Route::get('/payments/appointments/{appointmentId}/details', [PaymentApiController::class, 'paymentDetails'])->whereNumber('appointmentId');
    Route::post('/payments/appointments/{appointmentId}/receipt', [PaymentApiController::class, 'uploadReceipt'])->whereNumber('appointmentId');
    Route::get('/payments/appointments/{appointmentId}/receipt', [ReceiptApiController::class, 'show'])->whereNumber('appointmentId');
});
