<?php

declare(strict_types=1);

namespace App\Support\Errors;

class ExternalServiceException extends AppException
{
    public static function serviceError(string $service, ?string $message = null, array $context = []): self
    {
        $context['service'] = $service;

        return new self(ErrorCode::EXTERNAL_SERVICE_ERROR, $message, [], $context);
    }
}
