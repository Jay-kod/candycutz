<?php

declare(strict_types=1);

namespace App\Domain\Payment\DataObjects;

class GatewayCheckout
{
    public function __construct(
        public readonly string $reference,
        public readonly string $gateway,
        public readonly ?string $authorization_url = null,
        public readonly ?string $access_code = null,
        public readonly int $amount_kobo = 0,
        public readonly string $currency = 'NGN',
        public readonly array $meta = [],
    ) {}

    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'gateway' => $this->gateway,
            'authorization_url' => $this->authorization_url,
            'access_code' => $this->access_code,
            'amount_kobo' => $this->amount_kobo,
            'currency' => $this->currency,
            'meta' => $this->meta,
        ];
    }
}
