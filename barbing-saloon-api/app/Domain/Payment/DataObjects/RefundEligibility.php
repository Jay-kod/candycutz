<?php

declare(strict_types=1);

namespace App\Domain\Payment\DataObjects;

final readonly class RefundEligibility
{
    public function __construct(
        public bool $eligible,
        public int $percentage,
        public int $refundAmountMinor,
        public int $originalAmountMinor,
        public string $reason,
        public bool $requiresApproval,
        public string $type,
        public float $hoursUntilAppointment,
        public ?string $paymentProvider,
        public bool $isPaid,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'eligible' => $this->eligible,
            'percentage' => $this->percentage,
            'refund_amount_minor' => $this->refundAmountMinor,
            'refund_amount' => round($this->refundAmountMinor / 100, 2),
            'original_amount_minor' => $this->originalAmountMinor,
            'original_amount' => round($this->originalAmountMinor / 100, 2),
            'reason' => $this->reason,
            'requires_approval' => $this->requiresApproval,
            'type' => $this->type,
            'hours_until_appointment' => round($this->hoursUntilAppointment, 2),
            'payment_provider' => $this->paymentProvider,
            'is_paid' => $this->isPaid,
            'compassionate_available' => $this->isPaid && $this->refundAmountMinor < $this->originalAmountMinor,
        ];
    }
}
