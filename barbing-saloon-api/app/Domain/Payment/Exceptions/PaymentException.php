<?php

declare(strict_types=1);

namespace App\Domain\Payment\Exceptions;

use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentException extends Exception
{
    public function __construct(
        string $message,
        protected string $errorCode = 'PAYMENT_ERROR',
        protected int $statusCode = 400,
        /** @var array<string, mixed> */
        protected array $details = [],
        ?Exception $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<string, mixed> */
    public function getDetails(): array
    {
        return $this->details;
    }

    public static function notConfigured(string $message = 'Payment gateway is not properly configured.'): self
    {
        return new self($message, 'PAYMENT_NOT_CONFIGURED', 503);
    }

    public static function providerUnavailable(string $message = 'Payment provider is currently unavailable. Please retry shortly.'): self
    {
        return new self($message, 'PAYMENT_PROVIDER_UNAVAILABLE', 503);
    }

    public static function providerRejected(string $message = 'Payment was rejected by provider.'): self
    {
        return new self($message, 'PAYMENT_PROVIDER_REJECTED', 502);
    }

    public static function notAllowed(string $message = 'Payment action is not permitted for this appointment.'): self
    {
        return new self($message, 'PAYMENT_NOT_ALLOWED', 403);
    }

    public static function alreadyPaid(string $message = 'Appointment is already paid.'): self
    {
        return new self($message, 'PAYMENT_ALREADY_PAID', 409);
    }

    public static function amountMismatch(string $message = 'Payment amount mismatch detected.'): self
    {
        return new self($message, 'PAYMENT_AMOUNT_MISMATCH', 500);
    }

    public static function referenceUnknown(string $message = 'Payment reference not found.'): self
    {
        return new self($message, 'PAYMENT_REFERENCE_UNKNOWN', 404);
    }

    public static function invalidTransition(string $message = 'Invalid payment state transition.'): self
    {
        return new self($message, 'PAYMENT_INVALID_TRANSITION', 409);
    }

    public static function webhookSignatureInvalid(string $message = 'Invalid webhook signature.'): self
    {
        return new self($message, 'PAYMENT_WEBHOOK_SIGNATURE_INVALID', 400);
    }

    public static function webhookIpRejected(string $message = 'Unauthorized webhook source IP.'): self
    {
        return new self($message, 'PAYMENT_WEBHOOK_IP_REJECTED', 403);
    }

    public static function duplicatePaid(string $message = 'Duplicate payment received for appointment.'): self
    {
        return new self($message, 'PAYMENT_DUPLICATE_PAID', 409);
    }

    public static function methodRetired(string $message = 'This payment method has been retired.'): self
    {
        return new self($message, 'PAYMENT_METHOD_RETIRED', 410);
    }

    public static function detailsNotConfigured(string $message = 'Payment details not configured.'): self
    {
        return new self($message, 'PAYMENT_DETAILS_NOT_CONFIGURED', 422);
    }

    public static function notFound(string $message = 'Payment not found.'): self
    {
        return self::referenceUnknown($message);
    }

    public static function invalidState(string $message = 'Payment in invalid state.'): self
    {
        return new self($message, 'PAYMENT_INVALID_STATE', 400);
    }

    public function render(Request $request): JsonResponse
    {
        return ApiResponse::error(
            $this->getMessage(),
            $this->details,
            $this->statusCode,
            $this->errorCode
        );
    }
}
