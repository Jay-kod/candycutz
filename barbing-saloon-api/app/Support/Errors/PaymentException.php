<?php

declare(strict_types=1);

namespace App\Support\Errors;

class PaymentException extends AppException
{
    public static function webhookSignatureInvalid(?string $message = null): self
    {
        return new self(ErrorCode::PAYMENT_WEBHOOK_SIGNATURE_INVALID, $message);
    }

    public static function webhookNotConfigured(?string $message = null): self
    {
        return new self(ErrorCode::PAYMENT_WEBHOOK_NOT_CONFIGURED, $message);
    }

    public static function providerError(?string $message = null, array $context = []): self
    {
        return new self(ErrorCode::PAYMENT_PROVIDER_ERROR, $message, [], $context);
    }
}
