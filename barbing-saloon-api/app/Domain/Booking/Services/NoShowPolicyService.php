<?php

declare(strict_types=1);

namespace App\Domain\Booking\Services;

use App\Domain\Payment\Services\PricingService;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\Shared\Services\SalonClock;
use App\Models\Appointment;
use App\Models\NoShowRecord;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Computes configurable no-show deadlines, warning windows, grace limits,
 * and threshold consequences anchored to the salon clock.
 */
class NoShowPolicyService
{
    public const DEFAULT_DELAY_MINUTES = 15;

    public const DEFAULT_WARNING_LEAD_MINUTES = 5;

    public const DEFAULT_GRACE_LIMIT = 1;

    public const DEFAULT_GRACE_EXTENSION_MINUTES = 15;

    public const DEFAULT_THRESHOLD = 2;

    public const DEFAULT_THRESHOLD_WINDOW_DAYS = 90;

    /**
     * @return array{
     *   no_show_delay_minutes: int,
     *   no_show_warning_lead_minutes: int,
     *   no_show_grace_limit: int,
     *   no_show_grace_extension_minutes: int,
     *   no_show_threshold: int,
     *   no_show_threshold_window_days: int,
     *   no_show_auto_mark_enabled: bool,
     *   no_show_auto_forfeit_deposit: bool
     * }
     */
    public function getConfig(): array
    {
        $keys = [
            'no_show_delay_minutes',
            'no_show_warning_lead_minutes',
            'no_show_grace_limit',
            'no_show_grace_extension_minutes',
            'no_show_threshold',
            'no_show_threshold_window_days',
            'no_show_auto_mark_enabled',
            'no_show_auto_forfeit_deposit',
        ];

        $values = [];
        try {
            $values = Cache::remember('policies.no_show', 300, static fn (): array => Setting::query()
                ->whereIn('key', $keys)
                ->pluck('value', 'key')
                ->all());
        } catch (\Throwable) {
            // Fall back to safe defaults if settings or cache store is unavailable
        }

        $delay = max(1, (int) ($values['no_show_delay_minutes'] ?? self::DEFAULT_DELAY_MINUTES));
        $lead = max(1, min($delay - 1, (int) ($values['no_show_warning_lead_minutes'] ?? self::DEFAULT_WARNING_LEAD_MINUTES)));
        if ($delay <= 1) {
            $lead = 1;
        }

        return [
            'no_show_delay_minutes' => $delay,
            'no_show_warning_lead_minutes' => $lead,
            'no_show_grace_limit' => max(0, (int) ($values['no_show_grace_limit'] ?? self::DEFAULT_GRACE_LIMIT)),
            'no_show_grace_extension_minutes' => max(5, (int) ($values['no_show_grace_extension_minutes'] ?? self::DEFAULT_GRACE_EXTENSION_MINUTES)),
            'no_show_threshold' => max(1, (int) ($values['no_show_threshold'] ?? self::DEFAULT_THRESHOLD)),
            'no_show_threshold_window_days' => max(1, (int) ($values['no_show_threshold_window_days'] ?? self::DEFAULT_THRESHOLD_WINDOW_DAYS)),
            'no_show_auto_mark_enabled' => filter_var($values['no_show_auto_mark_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'no_show_auto_forfeit_deposit' => filter_var($values['no_show_auto_forfeit_deposit'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * Authoritative deadline after which a confirmed appointment is eligible for automatic no-show.
     */
    public function effectiveDeadline(Appointment $appointment): ?Carbon
    {
        $startAt = SalonClock::parseAppointmentStart($appointment);
        if ($startAt === null) {
            return null;
        }

        $config = $this->getConfig();
        $baseDeadline = $startAt->copy()->addMinutes($config['no_show_delay_minutes']);

        if ($appointment->grace_extended_until !== null) {
            $extended = Carbon::instance($appointment->grace_extended_until)->setTimezone(SalonClock::timezone());
            if ($extended->greaterThan($baseDeadline)) {
                return $extended;
            }
        }

        return $baseDeadline;
    }

    /**
     * Timestamp when the late-arrival warning should be dispatched to the customer.
     */
    public function warningTime(Appointment $appointment): ?Carbon
    {
        $deadline = $this->effectiveDeadline($appointment);
        if ($deadline === null) {
            return null;
        }

        $config = $this->getConfig();

        return $deadline->copy()->subMinutes($config['no_show_warning_lead_minutes']);
    }

    /**
     * Determine whether a customer may currently request a grace-period extension.
     *
     * @return array{allowed: bool, reason: string|null, remaining_extensions: int}
     */
    public function canCustomerExtend(Appointment $appointment, ?Carbon $now = null): array
    {
        $config = $this->getConfig();
        $limit = $config['no_show_grace_limit'];
        $used = (int) ($appointment->grace_extension_count ?? 0);
        $remaining = max(0, $limit - $used);

        $statusVal = $appointment->status instanceof AppointmentStatus
            ? $appointment->status->value
            : (string) $appointment->status;

        if ($statusVal !== AppointmentStatus::confirmed->value) {
            return [
                'allowed' => false,
                'reason' => 'Only confirmed appointments can be extended.',
                'remaining_extensions' => $remaining,
            ];
        }

        if ($limit <= 0 || $used >= $limit) {
            return [
                'allowed' => false,
                'reason' => 'Maximum grace period extensions reached for this appointment.',
                'remaining_extensions' => 0,
            ];
        }

        $startAt = SalonClock::parseAppointmentStart($appointment);
        $deadline = $this->effectiveDeadline($appointment);
        if ($startAt === null || $deadline === null) {
            return [
                'allowed' => false,
                'reason' => 'Appointment time could not be resolved.',
                'remaining_extensions' => $remaining,
            ];
        }

        $current = $now?->copy()->setTimezone(SalonClock::timezone()) ?? SalonClock::now();

        // Allow requesting extension from 60 minutes before start up until the current deadline
        $windowOpens = $startAt->copy()->subMinutes(60);
        if ($current->lessThan($windowOpens)) {
            return [
                'allowed' => false,
                'reason' => 'Grace extensions open within 60 minutes of your appointment.',
                'remaining_extensions' => $remaining,
            ];
        }

        if ($current->greaterThanOrEqualTo($deadline)) {
            return [
                'allowed' => false,
                'reason' => 'The check-in deadline has already elapsed.',
                'remaining_extensions' => $remaining,
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'remaining_extensions' => $remaining,
        ];
    }

    /**
     * Count un-overturned no-shows for a customer within the configured rolling window.
     */
    public function rollingNoShowCount(int $customerId, ?int $windowDays = null): int
    {
        $days = $windowDays ?? $this->getConfig()['no_show_threshold_window_days'];
        $cutoff = now()->subDays(max(1, $days));

        return NoShowRecord::query()
            ->where('customer_id', $customerId)
            ->where('marked_at', '>=', $cutoff)
            ->where(function ($q) {
                $q->whereNull('appeal_outcome')
                    ->orWhere('appeal_outcome', '!=', 'approved');
            })
            ->count();
    }

    /**
     * Resolve paid amount in kobo (minor units) eligible for forfeiture recording.
     */
    public function resolveDepositMinor(Appointment $appointment): int
    {
        $appointment->loadMissing('payment');
        $payment = $appointment->payment;

        if ($payment !== null && (int) ($payment->amount_minor ?? 0) > 0) {
            $minor = (int) $payment->amount_minor;
            $base = (int) ($payment->amount ?: 0);
            if ($base > 0 && $minor === $base * 100) {
                return $minor;
            }

            return $minor >= 100000 ? $minor : $minor * 100;
        }

        $naira = app(PricingService::class)->resolvedNairaAmount($appointment, $payment);

        return (int) round($naira * 100);
    }
}
