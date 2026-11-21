<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Exceptions\PaymentException;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentStateMachine
{
    /**
     * Map of allowed state transitions.
     *
     * @var array<string, array<int, string>>
     */
    protected array $transitions = [
        'initiated' => ['pending', 'successful', 'failed', 'abandoned', 'expired', 'awaiting_verification'],
        'pending' => ['successful', 'failed', 'abandoned', 'expired'],
        'awaiting_verification' => ['successful', 'rejected', 'failed'],
        'awaiting_transfer' => ['receipt_uploaded', 'rejected', 'failed', 'successful'],
        'receipt_uploaded' => ['under_review', 'rejected', 'failed'],
        'under_review' => ['verified', 'successful', 'rejected', 'failed'],
        'verified' => ['partially_refunded', 'refunded'],
        'successful' => ['partially_refunded', 'refunded'],
        'partially_refunded' => ['refunded'],
        'failed' => [],
        'abandoned' => ['successful'], // Allow late successful payment if customer pays before reconciliation
        'expired' => [],
        'rejected' => [],
        'refunded' => [],
    ];

    public function canTransition(PaymentStatus|string $from, PaymentStatus|string $to): bool
    {
        $fromVal = $from instanceof PaymentStatus ? $from->value : (string) $from;
        $toVal = $to instanceof PaymentStatus ? $to->value : (string) $to;

        if ($fromVal === $toVal) {
            return true;
        }

        $allowed = $this->transitions[$fromVal] ?? [];

        return in_array($toVal, $allowed, true);
    }

    /**
     * Validate and apply transition to payment model.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws PaymentException
     */
    public function transition(Payment $payment, PaymentStatus|string $targetStatus, array $attributes = []): Payment
    {
        $currentStatusVal = $payment->status instanceof PaymentStatus ? $payment->status->value : (string) $payment->status;
        $targetStatusVal = $targetStatus instanceof PaymentStatus ? $targetStatus->value : (string) $targetStatus;

        if ($currentStatusVal === $targetStatusVal) {
            if (! empty($attributes)) {
                $payment->update($attributes);
            }

            return $payment;
        }

        if (! $this->canTransition($currentStatusVal, $targetStatusVal)) {
            Log::warning("Illegal payment transition attempted: {$currentStatusVal} -> {$targetStatusVal} for payment {$payment->id}");
            throw PaymentException::invalidTransition("Cannot transition payment from '{$currentStatusVal}' to '{$targetStatusVal}'.");
        }

        $updateData = array_merge($attributes, [
            'status' => $targetStatusVal,
        ]);

        $payment->update($updateData);

        return $payment;
    }
}
