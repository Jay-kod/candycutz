<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Domain\Payment\DataObjects\RefundEligibility;
use App\Domain\Payment\Enums\AppointmentPaymentStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shared\Services\SalonClock;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class RefundPolicyEngine
{
    public const KEY_FULL_HOURS = 'refund_full_hours';

    public const KEY_PARTIAL_HOURS = 'refund_partial_hours';

    public const KEY_PARTIAL_PERCENT = 'refund_partial_percent';

    public const KEY_COMPASSIONATE_ENABLED = 'refund_compassionate_enabled';

    public const KEY_SLA_HOURS = 'refund_sla_hours';

    public const KEY_REQUIRE_EVIDENCE = 'refund_require_evidence_for_compassionate';

    /**
     * @return array<string, mixed>
     */
    public function getPolicy(): array
    {
        $keys = [
            self::KEY_FULL_HOURS,
            self::KEY_PARTIAL_HOURS,
            self::KEY_PARTIAL_PERCENT,
            self::KEY_COMPASSIONATE_ENABLED,
            self::KEY_SLA_HOURS,
            self::KEY_REQUIRE_EVIDENCE,
        ];

        $values = [];
        try {
            $values = Cache::remember('policies.refund', 300, static fn (): array => Setting::query()
                ->whereIn('key', $keys)
                ->pluck('value', 'key')
                ->all());
        } catch (\Throwable) {
            // Fall back to defaults
        }

        return [
            self::KEY_FULL_HOURS => max(0, (int) ($values[self::KEY_FULL_HOURS] ?? 24)),
            self::KEY_PARTIAL_HOURS => max(0, (int) ($values[self::KEY_PARTIAL_HOURS] ?? 6)),
            self::KEY_PARTIAL_PERCENT => max(0, min(100, (int) ($values[self::KEY_PARTIAL_PERCENT] ?? 50))),
            self::KEY_COMPASSIONATE_ENABLED => $this->toBool($values[self::KEY_COMPASSIONATE_ENABLED] ?? '1'),
            self::KEY_SLA_HOURS => max(1, (int) ($values[self::KEY_SLA_HOURS] ?? 48)),
            self::KEY_REQUIRE_EVIDENCE => $this->toBool($values[self::KEY_REQUIRE_EVIDENCE] ?? '0'),
        ];
    }

    /**
     * Evaluate refund eligibility for an appointment cancellation or post-cancellation request.
     */
    public function evaluate(
        Appointment $appointment,
        string $reasonCategory = 'changed_mind',
        bool $isCompassionate = false,
        ?Carbon $asOf = null,
    ): RefundEligibility {
        $appointment->loadMissing('payment');
        $policy = $this->getPolicy();

        $payment = $appointment->payment;
        $paidMinor = $this->resolvePaidMinor($appointment, $payment);
        $provider = $payment ? ($payment->provider ?: $payment->payment_method) : ($appointment->payment_method ?: null);

        $isPaid = $paidMinor > 0 && $this->isPaymentRefundableState($appointment, $payment);

        $scheduledAt = SalonClock::parseAppointmentStart($appointment);
        $now = ($asOf ?? SalonClock::now())->copy()->setTimezone(SalonClock::timezone());
        $hoursUntil = $scheduledAt
            ? (($scheduledAt->getTimestamp() - $now->getTimestamp()) / 3600.0)
            : 0.0;

        if (! $isPaid) {
            return new RefundEligibility(
                eligible: false,
                percentage: 0,
                refundAmountMinor: 0,
                originalAmountMinor: 0,
                reason: 'No verified online or paid deposit is attached to this appointment.',
                requiresApproval: false,
                type: 'standard',
                hoursUntilAppointment: $hoursUntil,
                paymentProvider: $provider,
                isPaid: false,
            );
        }

        // Remaining refundable balance (guards against partial already refunded)
        $alreadyRefundedMinor = (int) ($payment->refunded_minor ?? 0);
        $refundableCapMinor = max(0, $paidMinor - $alreadyRefundedMinor);

        if ($refundableCapMinor <= 0) {
            return new RefundEligibility(
                eligible: false,
                percentage: 0,
                refundAmountMinor: 0,
                originalAmountMinor: $paidMinor,
                reason: 'This payment has already been fully refunded.',
                requiresApproval: false,
                type: 'standard',
                hoursUntilAppointment: $hoursUntil,
                paymentProvider: $provider,
                isPaid: true,
            );
        }

        // Compassionate path (illness / emergency)
        if ($isCompassionate || in_array($reasonCategory, ['illness', 'emergency'], true)) {
            if (! $policy[self::KEY_COMPASSIONATE_ENABLED]) {
                // Fall back to standard time-based rules when compassionate mode is disabled
                return $this->evaluateStandardWindow($policy, $hoursUntil, $paidMinor, $refundableCapMinor, $provider, $reasonCategory);
            }

            return new RefundEligibility(
                eligible: true,
                percentage: 100,
                refundAmountMinor: $refundableCapMinor,
                originalAmountMinor: $paidMinor,
                reason: 'Compassionate refund (100% subject to admin review) for illness or emergency.',
                requiresApproval: true,
                type: 'compassionate',
                hoursUntilAppointment: $hoursUntil,
                paymentProvider: $provider,
                isPaid: true,
            );
        }

        // Shop/barber-initiated cancellation always qualifies for 100%
        if ($reasonCategory === 'barber_cancelled') {
            return new RefundEligibility(
                eligible: true,
                percentage: 100,
                refundAmountMinor: $refundableCapMinor,
                originalAmountMinor: $paidMinor,
                reason: '100% refund because the appointment was cancelled by the barber or salon.',
                requiresApproval: false,
                type: 'standard',
                hoursUntilAppointment: $hoursUntil,
                paymentProvider: $provider,
                isPaid: true,
            );
        }

        return $this->evaluateStandardWindow($policy, $hoursUntil, $paidMinor, $refundableCapMinor, $provider, $reasonCategory);
    }

    /**
     * Compute refundable paid amount in minor units (kobo), honoring both amount_minor and legacy Naira amount columns.
     */
    public function resolvePaidMinor(Appointment $appointment, ?Payment $payment = null): int
    {
        $payment ??= $appointment->payment;

        if ($payment) {
            if ((int) $payment->amount_minor > 0) {
                return (int) $payment->amount_minor;
            }
            if ((float) $payment->amount > 0) {
                return (int) round((float) $payment->amount * 100);
            }
        }

        if ($appointment->deposit_paid && (float) $appointment->deposit_amount > 0) {
            return (int) round((float) $appointment->deposit_amount * 100);
        }

        $paymentStatus = $appointment->payment_status instanceof AppointmentPaymentStatus
            ? $appointment->payment_status->value
            : (string) ($appointment->payment_status ?? '');

        if (in_array($paymentStatus, [AppointmentPaymentStatus::paid->value, AppointmentPaymentStatus::partially_paid->value, AppointmentPaymentStatus::refund_pending->value], true)) {
            $naira = (float) ($appointment->deposit_amount > 0 ? $appointment->deposit_amount : ($appointment->grand_total ?: $appointment->total_price ?: 0));

            return (int) round(max(0.0, $naira) * 100);
        }

        return 0;
    }

    /**
     * @param  array<string, mixed>  $policy
     */
    private function evaluateStandardWindow(
        array $policy,
        float $hoursUntil,
        int $paidMinor,
        int $refundableCapMinor,
        ?string $provider,
        string $reasonCategory,
    ): RefundEligibility {
        $fullHours = (int) $policy[self::KEY_FULL_HOURS];
        $partialHours = (int) $policy[self::KEY_PARTIAL_HOURS];
        $partialPercent = (int) $policy[self::KEY_PARTIAL_PERCENT];

        if ($hoursUntil >= $fullHours) {
            return new RefundEligibility(
                eligible: true,
                percentage: 100,
                refundAmountMinor: $refundableCapMinor,
                originalAmountMinor: $paidMinor,
                reason: "Cancelled {$fullHours}+ hours before appointment: 100% automatic refund applies.",
                requiresApproval: false,
                type: 'standard',
                hoursUntilAppointment: $hoursUntil,
                paymentProvider: $provider,
                isPaid: true,
            );
        }

        if ($hoursUntil >= $partialHours && $partialPercent > 0) {
            $calculatedMinor = (int) round($paidMinor * ($partialPercent / 100));
            $refundMinor = min($refundableCapMinor, max(0, $calculatedMinor));

            return new RefundEligibility(
                eligible: $refundMinor > 0,
                percentage: $partialPercent,
                refundAmountMinor: $refundMinor,
                originalAmountMinor: $paidMinor,
                reason: "Cancelled {$partialHours}–{$fullHours} hours before appointment: {$partialPercent}% automatic refund applies.",
                requiresApproval: false,
                type: 'standard',
                hoursUntilAppointment: $hoursUntil,
                paymentProvider: $provider,
                isPaid: true,
            );
        }

        $noRefundReason = $hoursUntil < 0
            ? 'Appointment start time has passed; standard cancellations do not qualify for an automatic refund.'
            : "Cancelled less than {$partialHours} hours before appointment: no automatic refund applies. If you experienced an illness or emergency, submit a compassionate refund request for review.";

        return new RefundEligibility(
            eligible: false,
            percentage: 0,
            refundAmountMinor: 0,
            originalAmountMinor: $paidMinor,
            reason: $noRefundReason,
            requiresApproval: $reasonCategory === 'no_show_dispute',
            type: 'standard',
            hoursUntilAppointment: $hoursUntil,
            paymentProvider: $provider,
            isPaid: true,
        );
    }

    private function isPaymentRefundableState(Appointment $appointment, ?Payment $payment): bool
    {
        if ($payment) {
            $status = $payment->status instanceof PaymentStatus ? $payment->status : PaymentStatus::tryFrom((string) $payment->status);
            if ($status !== null && in_array($status, [
                PaymentStatus::successful,
                PaymentStatus::verified,
                PaymentStatus::partially_refunded,
            ], true)) {
                return true;
            }
            if ($status === PaymentStatus::refunded) {
                return false;
            }
        }

        $apptPayStatus = $appointment->payment_status instanceof AppointmentPaymentStatus
            ? $appointment->payment_status->value
            : (string) ($appointment->payment_status ?? '');

        if (in_array($apptPayStatus, [AppointmentPaymentStatus::refunded->value], true)) {
            return false;
        }

        return $appointment->deposit_paid
            || in_array($apptPayStatus, [AppointmentPaymentStatus::paid->value, AppointmentPaymentStatus::partially_paid->value, AppointmentPaymentStatus::refund_pending->value], true);
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }
}
