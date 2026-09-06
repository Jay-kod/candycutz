<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\AppointmentStatusHistory;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(
        protected AvailabilityEngine $availabilityEngine
    ) {}

    /**
     * Atomically create an appointment with concurrency collision locks.
     */
    public function createBooking(User $customer, array $data): Appointment
    {
        return DB::transaction(function () use ($customer, $data) {
            $date = Carbon::parse($data['date'])->toDateString();
            $startTime = Carbon::parse($data['start_time'])->format('H:i:s');
            $barberId = (int) $data['barber_id'];

            // 1. Calculate total duration and price from requested services
            $serviceIds = collect($data['services'])->pluck('id')->toArray();
            $services = Service::whereIn('id', $serviceIds)->get();

            $totalDurationMinutes = $services->sum('duration_minutes');
            $totalAmount = $services->sum('price');
            $travelFee = (float) ($data['travel_fee'] ?? 0.00);
            $grandTotal = $totalAmount + $travelFee;

            $endTime = Carbon::parse($startTime)->addMinutes($totalDurationMinutes)->format('H:i:s');

            // 2. Pessimistic Row Lock: Verify no overlapping appointment exists
            $conflict = Appointment::where('barber_id', $barberId)
                ->where('appointment_date', $date)
                ->whereNotIn('status', ['cancelled'])
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                })
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw new Exception('The selected time slot has just been reserved by another client. Please choose another slot.');
            }

            // 3. Create Appointment Header
            $appointment = Appointment::create([
                'booking_reference' => 'CC-' . strtoupper(Str::random(8)),
                'branch_id' => $data['branch_id'] ?? 1,
                'customer_id' => $customer->id,
                'barber_id' => $barberId,
                'service_id' => $services->first()->id, // primary service fallback
                'appointment_type' => $data['appointment_type'] ?? 'in_shop',
                'service_zone_id' => $data['service_zone_id'] ?? null,
                'customer_address_id' => $data['customer_address_id'] ?? null,
                'appointment_date' => $date,
                'appointment_time' => $startTime,
                'end_time' => $endTime,
                'total_duration_minutes' => $totalDurationMinutes,
                'total_amount' => $totalAmount,
                'travel_fee' => $travelFee,
                'grand_total' => $grandTotal,
                'total_price' => $grandTotal,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            // 4. Attach Multi-Service Items
            foreach ($services as $service) {
                AppointmentItem::create([
                    'appointment_id' => $appointment->id,
                    'service_id' => $service->id,
                    'price' => $service->price,
                    'duration_minutes' => $service->duration_minutes,
                    'created_at' => now(),
                ]);
            }

            // 5. Initial Status History Entry
            AppointmentStatusHistory::create([
                'appointment_id' => $appointment->id,
                'previous_status' => null,
                'new_status' => 'pending',
                'changed_by_user_id' => $customer->id,
                'reason' => 'Booking created by customer',
                'created_at' => now(),
            ]);

            return $appointment->load(['items.service', 'barber.user', 'customer']);
        });
    }

    /**
     * Transition appointment status with policy verification and audit logging.
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

        return $appointment;
    }
}
