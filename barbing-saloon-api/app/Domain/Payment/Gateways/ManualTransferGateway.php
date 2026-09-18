<?php

declare(strict_types=1);

namespace App\Domain\Payment\Gateways;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\DataObjects\GatewayCheckout;
use App\Domain\Payment\DataObjects\GatewayResult;
use App\Domain\Payment\DataObjects\WebhookEvent;
use App\Models\Payment;
use RuntimeException;

class ManualTransferGateway implements PaymentGateway
{
    public function initiate(Payment $payment): GatewayCheckout
    {
        $barber = $payment->appointment?->barber;

        // Try barber profile bank details first, fallback to shop default
        $bankName = $barber?->bank_name ?: config('payments.gateways.manual_transfer.default_bank_name');
        $accountName = $barber?->account_name ?: config('payments.gateways.manual_transfer.default_account_name');
        $accountNumber = $barber?->account_number ?: config('payments.gateways.manual_transfer.default_account_number');

        $reference = $payment->transaction_ref ?? 'MANUAL_'.strtoupper(uniqid());

        return new GatewayCheckout(
            reference: $reference,
            gateway: 'manual_transfer',
            authorization_url: null, // No redirect
            access_code: null,
            amount_kobo: (int) $payment->amount,
            meta: [
                'bank_name' => $bankName,
                'account_name' => $accountName,
                'account_number' => $accountNumber,
                'instructions' => 'Please transfer the exact amount and upload the receipt in the app.',
            ]
        );
    }

    public function verify(string $reference): GatewayResult
    {
        // For manual transfers, the authoritative source is our own database.
        // It's verified manually by admin/barber in the Dashboard.
        $payment = Payment::where('transaction_ref', $reference)
            ->orWhere('gateway_reference', $reference)
            ->first();

        if (! $payment) {
            throw new RuntimeException("Payment reference {$reference} not found.");
        }

        return new GatewayResult(
            reference: $reference,
            status: $payment->status === 'successful' ? 'success' : ($payment->status === 'failed' ? 'failed' : 'pending'),
            amount_kobo: (int) $payment->amount,
            gateway: 'manual_transfer',
            gateway_event_id: 'internal_verify',
            raw_payload: ['internal_status' => $payment->status],
        );
    }

    public function handleWebhook(string $rawPayload, array $headers): WebhookEvent
    {
        // Manual transfers don't receive webhooks. This should never be called.
        throw new RuntimeException('Manual transfer gateway does not support webhooks.');
    }
}
