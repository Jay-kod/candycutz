<?php

declare(strict_types=1);

namespace App\Support\Errors;

use Throwable;

class AuthException extends AppException
{
    public static function invalidCredentials(?string $message = null): self
    {
        return new self(ErrorCode::AUTH_INVALID_CREDENTIALS, $message);
    }

    public static function tokenInvalid(?string $message = null): self
    {
        return new self(ErrorCode::AUTH_TOKEN_INVALID, $message);
    }

    public static function tokenExpired(?string $message = null): self
    {
        return new self(ErrorCode::AUTH_TOKEN_EXPIRED, $message);
    }

    public static function accountSuspended(?string $message = null): self
    {
        return new self(ErrorCode::AUTH_ACCOUNT_SUSPENDED, $message);
    }

    public static function forbiddenRole(?string $message = null): self
    {
        return new self(ErrorCode::AUTH_FORBIDDEN_ROLE, $message);
    }

    public static function forbiddenPolicy(?string $message = null): self
    {
        return new self(ErrorCode::AUTH_FORBIDDEN_POLICY, $message);
    }

    public static function socialVerificationFailed(?string $message = null, array $context = []): self
    {
        return new self(ErrorCode::AUTH_SOCIAL_VERIFICATION_FAILED, $message, [], $context);
    }
}
