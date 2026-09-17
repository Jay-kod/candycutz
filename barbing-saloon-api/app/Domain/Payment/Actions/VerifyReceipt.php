<?php

declare(strict_types=1);

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Services\PaymentService;
use App\Models\Payment;
use Illuminate\Validation\ValidationException;

class VerifyReceipt
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function execute(Payment $payment, bool $approve, ?string $reason = null): Payment
    {
        if ($payment->status !== 'under_review') {
            throw ValidationException::withMessages([
                'payment' => ["Cannot verify payment in status: {$payment->status}"],
            ]);
        }

        if ($approve) {
            // Confirm the payment using the same idempotent logic used by webhooks,
            // passing 'manual_verify' as the mock event ID.
            $this->paymentService->confirmPaymentFromWebhook(
                gatewayReference: $payment->gateway_reference ?? $payment->transaction_ref,
                eventId: 'manual_verify_' . time(),
                rawPayload: ['verified_by_admin' => true, 'notes' => $reason]
            );
        } else {
            $this->paymentService->recordFailedPaymentFromWebhook(
                gatewayReference: $payment->gateway_reference ?? $payment->transaction_ref,
                eventId: 'manual_reject_' . time(),
                rawPayload: ['rejected_by_admin' => true, 'notes' => $reason],
                errorMessage: $reason ?? 'Receipt rejected'
            );
        }

        return $payment->refresh();
    }
}
