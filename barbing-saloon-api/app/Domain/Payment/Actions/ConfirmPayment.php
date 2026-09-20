<?php

declare(strict_types=1);

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Services\PaymentService;
use App\Models\Payment;

class ConfirmPayment
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Confirm a payment from webhook with idempotency protection.
     *
     * @param  array<string, mixed>  $rawPayload
     */
    public function execute(string $gatewayReference, string $eventId, array $rawPayload): Payment
    {
        return $this->paymentService->confirmPaymentFromWebhook($gatewayReference, $eventId, $rawPayload);
    }
}