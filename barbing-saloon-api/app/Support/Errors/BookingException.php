<?php

declare(strict_types=1);

namespace App\Support\Errors;

class BookingException extends AppException
{
    public static function slotTaken(?string $message = null, array $context = []): self
    {
        return new self(ErrorCode::BOOKING_SLOT_TAKEN, $message, [], $context);
    }

    public static function outsideHours(?string $message = null): self
    {
        return new self(ErrorCode::BOOKING_OUTSIDE_HOURS, $message);
    }

    public static function holiday(?string $message = null): self
    {
        return new self(ErrorCode::BOOKING_HOLIDAY, $message);
    }

    public static function noticeTooShort(?string $message = null): self
    {
        return new self(ErrorCode::BOOKING_NOTICE_TOO_SHORT, $message);
    }

    public static function barberUnavailable(?string $message = null): self
    {
        return new self(ErrorCode::BOOKING_BARBER_UNAVAILABLE, $message);
    }

    public static function invalidTransition(?string $message = null, array $context = []): self
    {
        return new self(ErrorCode::BOOKING_INVALID_TRANSITION, $message, [], $context);
    }

    public static function failed(?string $message = null, array $context = []): self
    {
        return new self(ErrorCode::BOOKING_FAILED, $message, [], $context);
    }
}
