<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

use App\Models\Appointment;
use App\Models\Setting;
use Carbon\Carbon;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Support\Facades\Cache;

/**
 * Authoritative salon clock and date/time formatter anchored to the Keffi shop timezone.
 */
class SalonClock
{
    public const DEFAULT_TIMEZONE = 'Africa/Lagos';

    /**
     * Resolve the authoritative salon timezone, avoiding legacy placeholder zones.
     */
    public static function timezone(): string
    {
        $configured = (string) (config('app.shop_timezone') ?: config('app.timezone') ?: self::DEFAULT_TIMEZONE);

        try {
            $settingTz = Cache::remember('salon_clock:shop_timezone', 300, function (): string {
                return (string) (Setting::query()->where('key', 'shop_timezone')->value('value') ?? '');
            });

            if ($settingTz !== '' && $settingTz !== 'America/Los_Angeles') {
                $configured = $settingTz;
            }
        } catch (\Throwable) {
            // Keep resilient before migrations or during isolated unit runs
        }

        try {
            new DateTimeZone($configured);

            return $configured;
        } catch (\Throwable) {
            return self::DEFAULT_TIMEZONE;
        }
    }

    /**
     * Current Carbon timestamp in the authoritative salon timezone.
     */
    public static function now(): Carbon
    {
        return Carbon::now(self::timezone());
    }

    /**
     * Parse an appointment's scheduled date and time in the salon timezone.
     */
    public static function parseAppointmentStart(Appointment $appointment): ?Carbon
    {
        $rawDate = $appointment->appointment_date;
        $rawTime = trim((string) $appointment->appointment_time);

        if (! $rawDate || $rawTime === '') {
            return null;
        }

        $dateStr = $rawDate instanceof DateTimeInterface
            ? $rawDate->format('Y-m-d')
            : Carbon::parse((string) $rawDate)->toDateString();

        $normalizedTime = strlen($rawTime) === 5 ? "{$rawTime}:00" : substr($rawTime, 0, 8);

        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', "{$dateStr} {$normalizedTime}", self::timezone()) ?: null;
        } catch (\Throwable) {
            try {
                return Carbon::parse("{$dateStr} {$rawTime}", self::timezone());
            } catch (\Throwable) {
                return null;
            }
        }
    }

    /**
     * Format a date value for customer-facing display.
     */
    public static function formatDate(mixed $date): string
    {
        if (! $date) {
            return self::now()->format('M d, Y');
        }

        return Carbon::parse((string) ($date instanceof DateTimeInterface ? $date->format('Y-m-d') : $date), self::timezone())
            ->format('M d, Y');
    }

    /**
     * Format a time string (e.g. "14:00") into 12-hour salon display time.
     */
    public static function formatTime(?string $time): string
    {
        if (! $time) {
            return 'Scheduled Time';
        }

        return Carbon::parse($time, self::timezone())->format('g:i A');
    }

    /**
     * Format a timestamp in the salon timezone.
     */
    public static function formatTimestamp(?DateTimeInterface $timestamp): string
    {
        $carbon = $timestamp ? Carbon::instance($timestamp) : self::now();

        return $carbon->setTimezone(self::timezone())->format('M d, Y · g:i A');
    }
}
