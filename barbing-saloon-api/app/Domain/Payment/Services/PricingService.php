<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Models\Appointment;

class PricingService
{
    /**
     * Compute the amount due for an appointment in kobo (minor units).
     * The server is the sole authority; client-supplied amounts are ignored.
     */
    public function amountDue(Appointment $appointment): int
    {
        // grand_total is authoritative and includes travel_fee, tip, discount
        $totalMinor = (int) ($appointment->grand_total ?: $appointment->total_price ?: $appointment->total_amount ?: 0);

        if ($appointment->deposit_paid) {
            return 0;
        }

        $mode = (string) config('payments.mode', 'full');

        return match ($mode) {
            'deposit_fixed' => $this->calculateFixedDeposit($appointment, $totalMinor),
            'deposit_percent' => $this->calculatePercentDeposit($totalMinor),
            default => $totalMinor,
        };
    }

    protected function calculateFixedDeposit(Appointment $appointment, int $totalMinor): int
    {
        $configuredFixed = (int) config('payments.deposit_fixed_amount_minor', 0);
        $depositMinor = $configuredFixed > 0 ? $configuredFixed : (int) ($appointment->deposit_amount ?: 0);

        if ($depositMinor <= 0) {
            return $totalMinor;
        }

        return min($depositMinor, $totalMinor);
    }

    protected function calculatePercentDeposit(int $totalMinor): int
    {
        $percent = (int) config('payments.deposit_percent', 100);

        if ($percent <= 0 || $percent >= 100) {
            return $totalMinor;
        }

        return (int) intdiv($totalMinor * $percent, 100);
    }
}
