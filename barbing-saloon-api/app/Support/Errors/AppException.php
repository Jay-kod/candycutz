<?php

declare(strict_types=1);

namespace App\Support\Errors;

use RuntimeException;
use Throwable;

class AppException extends RuntimeException
{
    protected ErrorCode $errorCode;
    /** @var array<string, mixed> */
    protected array $details;
    /** @var array<string, mixed> */
    protected array $customContext;

    /**
     * @param  array<string, mixed>  $details
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        ErrorCode $errorCode,
        ?string $message = null,
        array $details = [],
        array $context = [],
        ?Throwable $previous = null
    ) {
        $this->errorCode = $errorCode;
        $this->details = $details;
        $this->customContext = $context;

        parent::__construct($message ?? $errorCode->userMessage(), $errorCode->status(), $previous);
    }

    public function getErrorCode(): ErrorCode
    {
        return $this->errorCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCustomContext(): array
    {
        return $this->customContext;
    }
}
