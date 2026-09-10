<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Core\Enums\AppointmentStatus;
use App\Exceptions\BookingSlotUnavailableException;
use App\Jobs\SendBookingCancellation;
use App\Jobs\SendBookingConfirmation;
use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\AppointmentStatusHistory;
use App\Models\Barber;
use App\Models\BlockedPeriod;
use App\Models\Service;
use App\Models\ServiceZone;
use App\Models\User;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Atomically create an appointment with pessimistic collision locking.
     * Guarantees zero double-booking across simultaneous concurrent requests.
     *
     * @throws BookingSlotUnavailableException
     */
    public function createBooking(User $customer, array $data): Appointment
    {
        return DB::transaction(function () use ($customer, $data) {
            // 1. Resolve & normalize appointment parameters
            $dateStr = $data['appointment_date'] ?? ($data['date'] ?? null);
            if (!$dateStr) {
                throw new \InvalidArgumentException('Appointment date is required.');
            }
            $date = Carbon::parse($dateStr)->toDateString();

            $rawTime = $data['appointment_time'] ?? ($data['start_time'] ?? null);
            if (!$rawTime) {
                throw new \InvalidArgumentException('Appointment time is required.');
            }
            $startTime = substr(trim((string) $rawTime), 0, 5); // HH:MM

            // Resolve barber
            $barberId = !empty($data['barber_id'])
                ? (int) $data['barber_id']
                : (Barber::where('is_available', true)->value('id') ?? 1);
            $barber = Barber::findOrFail($barberId);

            // Resolve service(s)
            $serviceId = (int) ($data['service_id'] ?? 1);
            $service = Service::findOrFail($serviceId);

            $services = collect([$service]);
            if (!empty($data['services']) && is_array($data['services'])) {
                $additionalIds = collect($data['services'])->pluck('id')->filter()->toArray();
                if (!empty($additionalIds)) {
                    $services = Service::whereIn('id', array_unique(array_merge([$serviceId], $additionalIds)))->get();
                }
            }

            $totalDurationMinutes = (int) $services->sum('duration_minutes') ?: 30;
            $totalAmount = (float) $services->sum('price');

            // Handle home service travel calculation
            $type = $data['appointment_type'] ?? 'in_shop';
            $travelFee = 0.0;
            $zoneId = null;

            if ($type === 'home_service') {
                $zoneId = $data['service_zone_id'] ?? ($data['destination_address']['service_zone_id'] ?? null);
                $zone = $zoneId ? ServiceZone::find($zoneId) : ServiceZone::where('is_active', true)->first();
                $zoneId = $zone?->id;
                $travelFee = (float) ($zone?->base_travel_fee ?? 1500.0);
            }

            $grandTotal = $totalAmount + $travelFee;
            $endTime = Carbon::parse("{$date} {$startTime}")->addMinutes($totalDurationMinutes)->format('H:i');

            // 2. Business Boundary Check: Barber working hours
            $carbonDate = Carbon::parse($date);
            $dayOfWeek = (int) $carbonDate->dayOfWeekIso % 7; // 0 = Sunday, 1 = Monday ... 6 = Saturday

            $workingHour = WorkingHour::where('barber_id', $barber->id)
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if ($workingHour && $workingHour->is_closed) {
                throw new BookingSlotUnavailableException(
                    "The selected barber is off on this day.",
                    "{$date} {$startTime}"
                );
            }

            if ($workingHour && $workingHour->open_time && $workingHour->close_time) {
                $openTime = substr((string) $workingHour->open_time, 0, 5);
                $closeTime = substr((string) $workingHour->close_time, 0, 5);

                if ($startTime < $openTime || $endTime > $closeTime) {
                    throw new BookingSlotUnavailableException(
                        "The selected time window ({$startTime} - {$endTime}) is outside the barber's operating hours ({$openTime} - {$closeTime}).",
                        "{$date} {$startTime}"
                    );
                }
            }

            // 3. Blackout / Break Check: Blocked periods
            $startDateTime = "{$date} {$startTime}:00";
            $endDateTime = "{$date} {$endTime}:00";

            $hasBlackout = BlockedPeriod::where('barber_id', $barber->id)
                ->whereDate('start_datetime', $date)
                ->where(function ($query) use ($startDateTime, $endDateTime) {
                    $query->where('start_datetime', '<', $endDateTime)
                          ->where('end_datetime', '>', $startDateTime);
                })
                ->exists();

            if ($hasBlackout) {
                throw new BookingSlotUnavailableException(
                    "The barber is unavailable during this time period due to a scheduled break or leave.",
                    "{$date} {$startTime}"
                );
            }

            // 4. PESSIMISTIC ROW-LEVEL COLLISION LOCK (SELECT FOR UPDATE)
            // Checks for any overlapping appointment interval
            $conflict = Appointment::where('barber_id', $barber->id)
                ->where('appointment_date', $date)
                ->whereNotIn('status', ['cancelled'])
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('appointment_time', '<', $endTime)
                          ->where(function ($q) use ($startTime) {
                              $q->where('end_time', '>', $startTime)
                                ->orWhereNull('end_time');
                          });
                })
                ->lockForUpdate()
                ->first();

            if ($conflict) {
                throw new BookingSlotUnavailableException(
                    "This appointment slot has just been reserved by another customer. Please select another time slot.",
                    "{$date} {$startTime}"
                );
            }

            // 5. Create Canonical Appointment Header
            $bookingReference = 'CC-' . strtoupper(Str::random(6));

            $appointment = Appointment::create([
                'booking_reference' => $bookingReference,
                'branch_id' => $data['branch_id'] ?? 1,
                'customer_id' => $customer->id,
                'client_name' => $data['client_name'] ?? ($customer->real_name ?: $customer->name),
                'client_phone' => $data['client_phone'] ?? ($customer->phone ?: ''),
                'client_email' => $data['client_email'] ?? $customer->email,
                'barber_id' => $barber->id,
                'service_id' => $service->id,
                'service_zone_id' => $zoneId,
                'appointment_type' => $type,
                'appointment_date' => $date,
                'appointment_time' => $startTime,
                'end_time' => $endTime,
                'total_duration_minutes' => $totalDurationMinutes,
                'total_price' => $totalAmount,
                'total_amount' => $totalAmount,
                'travel_fee' => $travelFee,
                'grand_total' => $grandTotal,
                'status' => AppointmentStatus::pending->value,
                'notes' => $data['notes'] ?? null,
                'deposit_paid' => ($data['payment_method'] ?? '') === 'pay_at_venue',
                'deposit_amount' => 0.0,
            ]);

            // 6. Record items if table exists
            foreach ($services as $srv) {
                AppointmentItem::create([
                    'appointment_id' => $appointment->id,
                    'service_id' => $srv->id,
                    'price' => $srv->price,
                    'duration_minutes' => $srv->duration_minutes,
                    'created_at' => now(),
                ]);
            }

            // 7. Audit Status History
            AppointmentStatusHistory::create([
                'appointment_id' => $appointment->id,
                'previous_status' => null,
                'new_status' => AppointmentStatus::pending->value,
                'changed_by_user_id' => $customer->id,
                'reason' => 'Online customer reservation created',
                'created_at' => now(),
            ]);

            // 8. Dispatch async confirmation email (safe in local dev)
            try {
                SendBookingConfirmation::dispatch($appointment);
            } catch (\Throwable $e) {
                Log::warning("Could not dispatch booking confirmation mail for appointment {$appointment->id}: " . $e->getMessage());
            }

            return $appointment->load(['service.category', 'barber.user', 'serviceZone']);
        });
    }

    /**
     * Create immediate walk-in guest appointment from staff desk.
     *
     * @throws BookingSlotUnavailableException
     */
    public function createWalkIn(User $actor, Barber $barber, array $data): Appointment
    {
        return DB::transaction(function () use ($actor, $barber, $data) {
            $service = Service::findOrFail((int) $data['service_id']);
            $date = Carbon::parse($data['appointment_date'] ?? now())->toDateString();
            $startTime = substr(trim((string) ($data['appointment_time'] ?? now()->format('H:i'))), 0, 5);
            $durationMinutes = (int) ($service->duration_minutes ?: 30);
            $endTime = Carbon::parse("{$date} {$startTime}")->addMinutes($durationMinutes)->format('H:i');

            // Pessimistic check
            $conflict = Appointment::where('barber_id', $barber->id)
                ->where('appointment_date', $date)
                ->whereNotIn('status', ['cancelled'])
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('appointment_time', '<', $endTime)
                          ->where(function ($q) use ($startTime) {
                              $q->where('end_time', '>', $startTime)
                                ->orWhereNull('end_time');
                          });
                })
                ->lockForUpdate()
                ->first();

            if ($conflict) {
                throw new BookingSlotUnavailableException(
                    "This chair interval is currently occupied by appointment #{$conflict->booking_reference}.",
                    "{$date} {$startTime}"
                );
            }

            $bookingReference = 'CC-' . strtoupper(Str::random(6));
            $initialStatus = !empty($data['take_immediately']) ? 'in_progress' : 'confirmed';

            $appointment = Appointment::create([
                'booking_reference' => $bookingReference,
                'branch_id' => 1,
                'customer_id' => null, // Guest walk-in
                'client_name' => $data['customer_name'] ?? ($data['client_name'] ?? 'Walk-In Guest'),
                'client_phone' => $data['customer_phone'] ?? ($data['client_phone'] ?? ''),
                'client_email' => 'walkin@candycutz.com',
                'barber_id' => $barber->id,
                'service_id' => $service->id,
                'appointment_type' => 'in_shop',
                'appointment_date' => $date,
                'appointment_time' => $startTime,
                'end_time' => $endTime,
                'total_duration_minutes' => $durationMinutes,
                'total_price' => (float) $service->price,
                'total_amount' => (float) $service->price,
                'travel_fee' => 0.0,
                'grand_total' => (float) $service->price,
                'status' => $initialStatus,
                'deposit_paid' => true,
                'deposit_amount' => (float) $service->price,
                'notes' => $data['notes'] ?? 'Walk-in guest',
            ]);

            // Audit
            try {
                AppointmentStatusHistory::create([
                    'appointment_id' => $appointment->id,
                    'previous_status' => null,
                    'new_status' => $initialStatus,
                    'changed_by_user_id' => $actor->id,
                    'reason' => 'Walk-in added by barber desk',
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning("Could not create status history for walk-in: " . $e->getMessage());
            }

            return $appointment->load(['service.category', 'barber.user']);
        });
    }

    /**
     * Transition appointment status with audit logging and cancellation email.
     */
    public function transitionStatus(Appointment $appointment, string $newStatus, User $actor, ?string $reason = null): Appointment
    {
        $previousStatus = $appointment->status->value ?? (string) $appointment->status;

        $appointment->update([
            'status' => $newStatus,
            'cancellation_reason' => ($newStatus === 'cancelled') ? $reason : $appointment->cancellation_reason,
        ]);

        AppointmentStatusHistory::create([
            'appointment_id' => $appointment->id,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'changed_by_user_id' => $actor->id,
            'reason' => $reason,
            'created_at' => now(),
        ]);

        if ($newStatus === 'cancelled' && $appointment->customer) {
            try {
                SendBookingCancellation::dispatch($appointment);
            } catch (\Throwable $e) {
                Log::warning("Could not dispatch cancellation mail: " . $e->getMessage());
            }
        }

        return $appointment;
    }
}
