<?php

declare(strict_types=1);

namespace App\Core\Http\Controllers\Api\V1;

use App\Core\Enums\AppointmentStatus;
use App\Core\Http\Response\ApiResponse;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\ServiceZone;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->role?->value ?? $user->role;

        $query = Appointment::query()->with(['service.category', 'barber.user', 'serviceZone']);

        if ($role === 'barber' && $user->barber) {
            $query->where('barber_id', $user->barber->id);
        } elseif ($role !== 'admin' && $role !== 'super_admin') {
            $query->where('customer_id', $user->id);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $status = $request->status;
            if ($status === 'upcoming') {
                $query->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
                    ->whereDate('appointment_date', '>=', now()->toDateString());
            } elseif ($status === 'completed') {
                $query->where('status', AppointmentStatus::completed->value);
            } elseif ($status === 'cancelled') {
                $query->where('status', AppointmentStatus::cancelled->value);
            }
        }

        $appointments = $query->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate((int) $request->input('per_page', 15));

        $data = $appointments->getCollection()->map(fn (Appointment $a) => $this->formatAppointment($a));

        return ApiResponse::success([
            'items' => $data,
            'pagination' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ],
        ], 'Appointments retrieved successfully');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $role = $user->role?->value ?? $user->role;

        $appointment = Appointment::query()
            ->with(['service.category', 'barber.user', 'serviceZone'])
            ->find($id);

        if (!$appointment) {
            return ApiResponse::error("Appointment #{$id} not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        // Authorization check
        $isOwner = $appointment->customer_id === $user->id;
        $isBarber = $user->barber && $appointment->barber_id === $user->barber->id;
        $isAdmin = in_array($role, ['admin', 'super_admin'], true);

        if (!$isOwner && !$isBarber && !$isAdmin) {
            return ApiResponse::error('You do not have permission to view this appointment.', [], 403, 'FORBIDDEN_ROLE');
        }

        return ApiResponse::success($this->formatAppointment($appointment), 'Appointment details retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Allow start_time or appointment_time
        if ($request->has('start_time') && !$request->has('appointment_time')) {
            $request->merge(['appointment_time' => $request->start_time]);
        }

        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'barber_id' => ['nullable', 'integer', 'exists:barbers,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'string'],
            'appointment_type' => ['nullable', 'string', 'in:in_shop,home_service'],
            'destination_address' => ['nullable', 'array'],
            'payment_method' => ['nullable', 'string', 'in:pay_at_venue,stripe,wallet'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service = Service::findOrFail((int) $validated['service_id']);
        $type = $validated['appointment_type'] ?? 'in_shop';
        $time = substr(trim((string) $validated['appointment_time']), 0, 5);
        $date = Carbon::parse($validated['appointment_date'])->toDateString();

        // Assign barber
        $barberId = !empty($validated['barber_id'])
            ? (int) $validated['barber_id']
            : (Barber::where('is_available', true)->value('id') ?? 1);

        $barber = Barber::findOrFail($barberId);

        // Transaction with concurrency lock to prevent double booking
        $appointment = DB::transaction(function () use ($user, $service, $barber, $date, $time, $type, $validated) {
            // Lock and check for existing overlapping bookings
            $existing = Appointment::where('barber_id', $barber->id)
                ->where('appointment_date', $date)
                ->where('appointment_time', $time)
                ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'This appointment time has just been reserved by another customer. Please select another slot.',
                    'error' => [
                        'code' => 'BOOKING_SLOT_UNAVAILABLE',
                        'message' => 'This appointment time has just been reserved by another customer.',
                        'details' => ['slot' => ["{$date} {$time}"]],
                    ],
                    'code' => 422,
                ], 422));
            }

            $travelFee = 0.0;
            $zoneId = null;
            if ($type === 'home_service') {
                $zoneId = $validated['destination_address']['service_zone_id'] ?? null;
                $zone = $zoneId ? ServiceZone::find($zoneId) : ServiceZone::where('is_active', true)->first();
                $travelFee = (float) ($zone?->base_travel_fee ?? 1500.0);
            }

            $price = (float) $service->price;
            $grandTotal = $price + $travelFee;
            $durationMinutes = (int) ($service->duration_minutes ?? 30);
            $endTime = Carbon::parse("{$date} {$time}")->addMinutes($durationMinutes)->format('H:i');

            $bookingReference = 'CC-' . strtoupper(Str::random(6));

            return Appointment::create([
                'booking_reference' => $bookingReference,
                'customer_id' => $user->id,
                'client_name' => $user->name,
                'client_phone' => $user->phone ?? '',
                'client_email' => $user->email,
                'barber_id' => $barber->id,
                'service_id' => $service->id,
                'service_zone_id' => $zoneId,
                'appointment_type' => $type,
                'appointment_date' => $date,
                'appointment_time' => $time,
                'end_time' => $endTime,
                'total_duration_minutes' => $durationMinutes,
                'total_price' => $price,
                'total_amount' => $price,
                'travel_fee' => $travelFee,
                'grand_total' => $grandTotal,
                'status' => AppointmentStatus::pending->value,
                'notes' => $validated['notes'] ?? null,
                'deposit_paid' => ($validated['payment_method'] ?? '') === 'pay_at_venue',
                'deposit_amount' => 0.0,
            ]);
        });

        $appointment->load(['service.category', 'barber.user', 'serviceZone']);

        return ApiResponse::success($this->formatAppointment($appointment), 'Appointment reserved successfully.', 201);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $role = $user->role?->value ?? $user->role;

        $appointment = Appointment::findOrFail($id);

        $isOwner = $appointment->customer_id === $user->id;
        $isBarber = $user->barber && $appointment->barber_id === $user->barber->id;
        $isAdmin = in_array($role, ['admin', 'super_admin'], true);

        if (!$isOwner && !$isBarber && !$isAdmin) {
            return ApiResponse::error('You do not have permission to cancel this appointment.', [], 403, 'FORBIDDEN_ROLE');
        }

        $currentStatus = $appointment->status?->value ?? $appointment->status;
        if (in_array($currentStatus, ['completed', 'cancelled'], true)) {
            return ApiResponse::error("Cannot cancel an appointment that is already {$currentStatus}.", [], 422, 'INVALID_TRANSITION');
        }

        $reason = $request->input('reason', 'Customer requested cancellation');

        $appointment->update([
            'status' => AppointmentStatus::cancelled->value,
            'cancellation_reason' => $reason,
        ]);

        $appointment->load(['service.category', 'barber.user', 'serviceZone']);

        return ApiResponse::success($this->formatAppointment($appointment), 'Appointment cancelled successfully.');
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $role = $user->role?->value ?? $user->role;

        $appointment = Appointment::findOrFail($id);

        $isBarber = $user->barber && $appointment->barber_id === $user->barber->id;
        $isAdmin = in_array($role, ['admin', 'super_admin'], true);

        if (!$isBarber && !$isAdmin) {
            return ApiResponse::error('Only assigned barbers and administrators can update operational status.', [], 403, 'FORBIDDEN_ROLE');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:confirmed,checked_in,in_progress,completed,no_show,cancelled'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        // Map operational statuses safely to the database enum
        $dbStatus = match ($validated['status']) {
            'checked_in', 'in_progress' => AppointmentStatus::confirmed->value,
            'completed' => AppointmentStatus::completed->value,
            'no_show' => AppointmentStatus::no_show->value,
            'cancelled' => AppointmentStatus::cancelled->value,
            default => AppointmentStatus::confirmed->value,
        };

        $notes = $appointment->notes;
        if (!empty($validated['reason'])) {
            $notes = ($notes ? "{$notes} | " : '') . "Status update ({$validated['status']}): {$validated['reason']}";
        }

        $appointment->update([
            'status' => $dbStatus,
            'notes' => $notes,
        ]);

        $appointment->load(['service.category', 'barber.user', 'serviceZone']);

        return ApiResponse::success($this->formatAppointment($appointment), "Appointment status updated to {$validated['status']}.");
    }

    public function storeWalkIn(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:100'],
            'client_phone' => ['nullable', 'string', 'max:30'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'appointment_date' => ['nullable', 'date'],
            'appointment_time' => ['nullable', 'string'],
        ]);

        $service = Service::findOrFail((int) $validated['service_id']);
        $barberId = $barber?->id ?? Barber::value('id') ?? 1;

        $date = $validated['appointment_date'] ?? now()->toDateString();
        $time = $validated['appointment_time'] ?? now()->format('H:i');
        $durationMinutes = (int) ($service->duration_minutes ?? 30);
        $endTime = Carbon::parse("{$date} {$time}")->addMinutes($durationMinutes)->format('H:i');

        $bookingReference = 'WALK-' . strtoupper(Str::random(5));

        $appointment = Appointment::create([
            'booking_reference' => $bookingReference,
            'client_name' => $validated['client_name'],
            'client_phone' => $validated['client_phone'] ?? '08000000000',
            'client_email' => 'walkin@candycutz.com',
            'barber_id' => $barberId,
            'service_id' => $service->id,
            'appointment_type' => 'in_shop',
            'appointment_date' => $date,
            'appointment_time' => $time,
            'end_time' => $endTime,
            'total_duration_minutes' => $durationMinutes,
            'total_price' => (float) $service->price,
            'total_amount' => (float) $service->price,
            'grand_total' => (float) $service->price,
            'status' => AppointmentStatus::confirmed->value,
            'notes' => 'Rapid Walk-In Client',
            'deposit_paid' => true,
        ]);

        $appointment->load(['service.category', 'barber.user', 'serviceZone']);

        return ApiResponse::success($this->formatAppointment($appointment), 'Walk-in client booked successfully.', 201);
    }

    protected function formatAppointment(Appointment $a): array
    {
        $service = $a->service;
        $barber = $a->barber;
        $statusVal = $a->status?->value ?? $a->status;

        return [
            'id' => $a->id,
            'booking_reference' => $a->booking_reference ?? "CC-{$a->id}",
            'customer_id' => $a->customer_id,
            'client_name' => $a->client_name,
            'client_phone' => $a->client_phone,
            'client_email' => $a->client_email,
            'appointment_date' => Carbon::parse($a->appointment_date)->toDateString(),
            'start_time' => substr((string) $a->appointment_time, 0, 5),
            'end_time' => $a->end_time ? substr((string) $a->end_time, 0, 5) : null,
            'appointment_type' => $a->appointment_type ?? 'in_shop',
            'status' => $statusVal,
            'payment_status' => $a->deposit_paid ? 'paid' : 'pending',
            'total_duration_minutes' => (int) ($a->total_duration_minutes ?? $service?->duration_minutes ?? 30),
            'subtotal' => (float) ($a->total_price ?? $a->total_amount ?? $service?->price ?? 0),
            'home_service_surcharge' => (float) ($a->travel_fee ?? 0),
            'discount_amount' => (float) ($a->discount_amount ?? 0),
            'grand_total' => (float) ($a->grand_total ?? $a->total_price ?? $service?->price ?? 0),
            'notes' => $a->notes,
            'service' => $service ? [
                'id' => $service->id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'duration_minutes' => (int) $service->duration_minutes,
                'category' => $service->category?->name ?? 'Grooming',
            ] : null,
            'barber' => $barber ? [
                'id' => $barber->id,
                'name' => $barber->user?->name ?? 'Master Barber',
                'username' => $barber->user?->username ?? 'barber',
                'rating' => (float) ($barber->rating ?? 5.0),
                'chair_status' => $barber->chair_status ?? 'free',
            ] : null,
            'service_zone' => $a->serviceZone ? [
                'id' => $a->serviceZone->id,
                'name' => $a->serviceZone->name,
                'surcharge' => (float) ($a->serviceZone->base_travel_fee ?? 0),
            ] : null,
            'created_at' => $a->created_at?->toIso8601String(),
        ];
    }
}
