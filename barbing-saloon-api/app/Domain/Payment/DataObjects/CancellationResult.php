<?php

declare(strict_types=1);

namespace App\Domain\Payment\DataObjects;

use App\Models\Appointment;
use App\Models\RefundRequest;

final readonly class CancellationResult
{
    public function __construct(
        public Appointment $appointment,
        public RefundEligibility $eligibility,
        public ?RefundRequest $refundRequest = null,
    ) {}
}
