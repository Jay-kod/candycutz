<?php

declare(strict_types=1);

namespace App\Domain\Payment\DataObjects;

class GatewayResult
{
    public function __construct(
        public readonly string $reference,
        public readonly string $status, // 'success', 'failed', 'pending'
        public readonly int $amount_kobo,
        public readonly string $gateway,
        public readonly ?string $gateway_event_id = null,
        public readonly string $currency = 'NGN',
        public readonly array $raw_payload = [],
    ) {}

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'status' => $this->status,
            'amount_kobo' => $this->amount_kobo,
            'gateway' => $this->gateway,
            'gateway_event_id' => $this->gateway_event_id,
            'currency' => $this->currency,
        ];
    }
}
