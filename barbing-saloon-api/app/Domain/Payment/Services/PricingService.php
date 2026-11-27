<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Models\Appointment;
use App\Models\Payment;

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

    /**
     * Resolve the authoritative Naira amount from a payment or appointment,
     * normalizing both Naira integers and kobo minor-unit values consistently.
     */
    public function resolvedNairaAmount(Appointment $appointment, ?Payment $payment = null): float
    {
        $payment ??= $appointment->relationLoaded('payment') ? $appointment->payment : $appointment->payment()->first();

        if ($payment !== null && (int) ($payment->amount_minor ?? 0) > 0) {
            $minor = (int) $payment->amount_minor;
            $base = (int) ($payment->amount ?: $appointment->grand_total ?: 0);
            if ($base > 0 && $minor === $base * 100) {
                return (float) $base;
            }

            return $minor >= 100000 ? ($minor / 100) : (float) $minor;
        }

        if ($payment !== null && (int) $payment->amount > 0) {
            $amt = (int) $payment->amount;

            return $amt >= 100000 ? ($amt / 100) : (float) $amt;
        }

        $raw = (int) ($appointment->grand_total ?: $appointment->total_price ?: $appointment->total_amount ?: 0);

        return $raw >= 100000 ? ($raw / 100) : (float) $raw;
    }

    /**
     * Format a Naira amount with 2 decimal places (without currency symbol).
     */
    public function formatNaira(float|int $amountNaira): string
    {
        return number_format((float) $amountNaira, 2);
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
