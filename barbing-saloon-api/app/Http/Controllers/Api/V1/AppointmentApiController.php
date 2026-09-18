<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Booking\DataObjects\BookingData;
use App\Domain\Booking\Services\BookingService;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Exceptions\BookingSlotUnavailableException;
use App\Http\Resources\AppointmentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\Barber;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentApiController
{
    use AuthorizesRequests;

    public function __construct(
        protected BookingService $bookingService
    ) {}

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

        $data = AppointmentResource::collection($appointments->getCollection());

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
        $appointment = Appointment::query()
            ->with(['service.category', 'barber.user', 'serviceZone'])
            ->findOrFail($id);

        $this->authorize('view', $appointment);

        return ApiResponse::success(new AppointmentResource($appointment), 'Appointment details retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Allow start_time or appointment_time
        if ($request->has('start_time') && ! $request->has('appointment_time')) {
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

        try {
            $appointment = $this->bookingService->createBooking(
                $user,
                BookingData::fromRequest($request)
            );

            return ApiResponse::success(new AppointmentResource($appointment), 'Appointment reserved successfully.', 201);
        } catch (BookingSlotUnavailableException $e) {
            return $e->render($request);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 422, 'BOOKING_FAILED');
        }
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $appointment = Appointment::findOrFail($id);

        $this->authorize('cancel', $appointment);

        $currentStatus = $appointment->status?->value ?? (string) $appointment->status;
        if (in_array($currentStatus, ['completed', 'cancelled'], true)) {
            return ApiResponse::error("Cannot cancel an appointment that is already {$currentStatus}.", [], 422, 'INVALID_TRANSITION');
        }

        $reason = $request->input('reason', 'Customer requested cancellation');

        $this->bookingService->transitionStatus($appointment, AppointmentStatus::cancelled->value, $user, $reason);

        $appointment->load(['service.category', 'barber.user', 'serviceZone']);

        return ApiResponse::success(new AppointmentResource($appointment), 'Appointment cancelled successfully.');
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $appointment = Appointment::findOrFail($id);

        $this->authorize('manageForBarber', $appointment);

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

        $reason = $validated['reason'] ?? "Status updated to {$validated['status']}";
        $this->bookingService->transitionStatus($appointment, $dbStatus, $user, $reason);

        $appointment->load(['service.category', 'barber.user', 'serviceZone']);

        return ApiResponse::success(new AppointmentResource($appointment), "Appointment status updated to {$validated['status']}.");
    }

    public function storeWalkIn(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        $validated = $request->validate([
            'client_name' => ['nullable', 'string', 'max:100'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'client_phone' => ['nullable', 'string', 'max:30'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'barber_id' => ['nullable', 'integer', 'exists:barbers,id'],
            'appointment_date' => ['nullable', 'date'],
            'appointment_time' => ['nullable', 'string'],
            'start_time' => ['nullable', 'string'],
            'take_immediately' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $barberId = $barber?->id ?? ($validated['barber_id'] ?? (Barber::value('id') ?? 1));
        $targetBarber = Barber::findOrFail((int) $barberId);

        try {
            $appointment = $this->bookingService->createWalkIn(
                $user,
                $targetBarber,
                BookingData::fromRequest($request)
            );

            return ApiResponse::success(new AppointmentResource($appointment), 'Walk-in client booked successfully.', 201);
        } catch (BookingSlotUnavailableException $e) {
            return $e->render($request);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 422, 'BOOKING_FAILED');
        }
    }
}
