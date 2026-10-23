<?php

declare(strict_types=1);

namespace App\Domain\Payment\Contracts;

use App\Domain\Payment\DataObjects\GatewayCheckout;
use App\Domain\Payment\DataObjects\GatewayResult;
use App\Domain\Payment\DataObjects\WebhookEvent;
use App\Models\Payment;

interface PaymentGateway
{
    /**
     * Initiate a payment checkout session.
     *
     * Returns the authorization URL (for redirect gateways like Paystack)
     * or bank details (for manual transfer).
     */
    public function initiate(Payment $payment): GatewayCheckout;

    /**
     * Verify a payment by its reference.
     *
     * Queries the gateway's API to confirm the authoritative payment status.
     */
    public function verify(string $reference): GatewayResult;

    /**
     * Refund a successful payment through the provider.
     *
     * Returns a structured result so the caller can apply the local payment state
     * only after the provider confirms the refund has been accepted.
     */
    public function refund(Payment $payment, int $amountMinor, string $reason = 'Customer refund requested by admin'): GatewayResult;

    /**
     * Parse and verify an incoming webhook payload.
     *
     * Validates the signature/authenticity and returns a structured event.
     * Throws on invalid signature.
     *
     * @param  array<string, mixed>  $headers
     */
    public function handleWebhook(string $rawPayload, array $headers): WebhookEvent;
}
