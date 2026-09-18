<?php

declare(strict_types=1);

namespace App\Domain\Payment\DataObjects;

class WebhookEvent
{
    /**
     * @param  array<string, mixed>  $raw_payload
     */
    public function __construct(
        public readonly string $event_type,
        public readonly string $reference,
        public readonly string $gateway_event_id,
        public readonly int $amount_kobo,
        public readonly string $status, // 'success', 'failed'
        public readonly string $gateway,
        public readonly array $raw_payload = [],
    ) {}

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }
}
