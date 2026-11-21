<?php

declare(strict_types=1);

namespace App\Domain\Payment\Enums;

enum ManualTransferState: string
{
    case awaiting_transfer = 'awaiting_transfer';
    case receipt_uploaded = 'receipt_uploaded';
    case under_review = 'under_review';
    case verified = 'verified';
    case rejected = 'rejected';

    /**
     * Determine if a transition from this state to target state is allowed.
     */
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::awaiting_transfer => in_array($target, [self::receipt_uploaded, self::rejected]),
            self::receipt_uploaded => in_array($target, [self::under_review, self::rejected]),
            self::under_review => in_array($target, [self::verified, self::rejected]),
            self::verified => false, // terminal state
            self::rejected => in_array($target, [self::receipt_uploaded]), // customer can re-upload if rejected
        };
    }
}
