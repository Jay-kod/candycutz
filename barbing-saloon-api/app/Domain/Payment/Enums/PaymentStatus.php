<?php

declare(strict_types=1);

namespace App\Domain\Payment\Enums;

enum PaymentStatus: string
{
    case initiated = 'initiated';
    case pending = 'pending';
    case successful = 'successful';
    case failed = 'failed';
    case abandoned = 'abandoned';
    case expired = 'expired';
    case partially_refunded = 'partially_refunded';
    case refunded = 'refunded';
    case awaiting_verification = 'awaiting_verification';
    case rejected = 'rejected';

    // Manual transfer lifecycle states
    case awaiting_transfer = 'awaiting_transfer';
    case receipt_uploaded = 'receipt_uploaded';
    case under_review = 'under_review';
    case verified = 'verified';

    public function isFinal(): bool
    {
        return in_array($this, [
            self::successful, self::failed, self::abandoned,
            self::expired, self::refunded, self::rejected, self::verified,
        ], true);
    }

    public function isSuccess(): bool
    {
        return in_array($this, [self::successful, self::partially_refunded, self::refunded, self::verified], true);
    }
}
