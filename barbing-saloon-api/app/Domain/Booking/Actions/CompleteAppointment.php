<?php

declare(strict_types=1);

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\AppointmentStateMachine;
use App\Domain\Booking\Services\BookingService;
use App\Domain\Shared\Enums\AppointmentSource;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use App\Support\Errors\AppException;
use App\Support\Errors\ErrorCode;

class CompleteAppointment
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Verify customer service code (for online bookings) and complete the appointment.
     */
    public function execute(Appointment $appointment, User $actor, ?string $verificationCode = null, ?string $reason = null): Appointment
    {
        $previousStatus = $appointment->status instanceof AppointmentStatus
            ? $appointment->status->value
            : (string) $appointment->status;

        if (! AppointmentStateMachine::isAllowed($previousStatus, AppointmentStatus::completed->value)) {
            return $this->bookingService->transitionStatus(
                $appointment,
                AppointmentStatus::completed->value,
                $actor,
                $reason ?: 'Completed by barber'
            );
        }

        if (! $this->isWalkIn($appointment)) {
            $provided = $this->normalizeCode((string) ($verificationCode ?? ''));

            if ($provided === '') {
                throw new AppException(
                    ErrorCode::VALIDATION_FAILED,
                    'Verification code is required to complete this booking.',
                    ['verification_code' => ['Verification code is required to complete this booking.']]
                );
            }

            if (! $this->codeMatches($appointment, $provided)) {
                throw new AppException(
                    ErrorCode::VALIDATION_FAILED,
                    'Invalid verification code. Please check the customer\'s booking code and try again.',
                    ['verification_code' => ['Invalid verification code. Please check the customer\'s booking code and try again.']]
                );
            }
        }

        return $this->bookingService->transitionStatus(
            $appointment,
            AppointmentStatus::completed->value,
            $actor,
            $reason ?: 'Completed by barber'
        );
    }

    private function isWalkIn(Appointment $appointment): bool
    {
        $rawSource = $appointment->getAttributes()['source'] ?? null;

        return $appointment->customer_id === null
            || $rawSource === AppointmentSource::walk_in->value
            || str_contains((string) $appointment->client_name, 'Walk-In');
    }

    private function normalizeCode(string $raw): string
    {
        $cleaned = preg_replace('/\s+/', '', trim($raw)) ?? '';

        return strtoupper(ltrim($cleaned, '#'));
    }

    private function codeMatches(Appointment $appointment, string $provided): bool
    {
        $expected = $this->normalizeCode((string) $appointment->verification_code);

        if ($provided === $expected) {
            return true;
        }

        // Allow entering the 6-character suffix alone when the generated reference uses the CC- prefix.
        if (str_starts_with($expected, 'CC-') && ! str_contains($provided, '-')) {
            return ('CC-'.$provided) === $expected;
        }

        return false;
    }
}
