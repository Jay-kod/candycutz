<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Booking\Actions\CreateBooking;
use App\Domain\Booking\Actions\CreateWalkInAppointment;
use App\Domain\Booking\Actions\GetAppointments;
use App\Domain\Booking\DataObjects\BookingData;
use App\Domain\Booking\Services\BookingService;
use App\Domain\Shared\Enums\AppointmentSource;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Exceptions\BookingSlotUnavailableException;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\StoreWalkInRequest;
use App\Http\Requests\UpdateAppointmentStatusRequest;
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
        protected CreateBooking $createBooking,
        protected CreateWalkInAppointment $createWalkIn,
        protected BookingService $bookingService
    ) {}

    public function index(Request $request, GetAppointments $action): JsonResponse
    {
        $source = $request->query('source');
        if ($source && in_array($source, ['web', 'app', 'walk_in'], true)) {
            $source = AppointmentSource::tryFrom($source);
        }

        $appointments = $action->execute(
            $request->user(),
            $request->query('status'),
            (int) $request->input('per_page', 15),
            $source
        );

        $items = AppointmentResource::collection($appointments->getCollection());

        return response()->json([
            'success' => true,
            'message' => 'Appointments retrieved successfully',
            'data' => $items,
            'items' => $items,
            'pagination' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ],
        ], 200);
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('complete', $appointment);

        $reason = $request->input('reason', 'Completed by barber');
        $this->bookingService->transitionStatus($appointment, AppointmentStatus::completed->value, $request->user(), $reason);

        return ApiResponse::success(new AppointmentResource($appointment->load(['service.category', 'barber.user', 'serviceZone'])), 'Appointment marked as completed.');
    }

    public function noShow(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('markNoShow', $appointment);

        $reason = $request->input('reason', 'Marked no-show by barber');
        $this->bookingService->transitionStatus($appointment, AppointmentStatus::no_show->value, $request->user(), $reason);

        return ApiResponse::success(new AppointmentResource($appointment->load(['service.category', 'barber.user', 'serviceZone'])), 'Appointment marked as no-show.');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::query()
            ->with(['service.category', 'barber.user', 'serviceZone'])
            ->findOrFail($id);

        $this->authorize('view', $appointment);

        return ApiResponse::success(new AppointmentResource($appointment), 'Appointment details retrieved');
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        try {
            $appointment = $this->createBooking->execute(
                $request->user(),
                BookingData::fromRequest($request),
                \App\Domain\Shared\Enums\AppointmentSource::app
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
        $appointment = Appointment::findOrFail($id);
        $this->authorize('cancel', $appointment);

        $currentStatus = $appointment->status->value;
        if (in_array($currentStatus, ['completed', 'cancelled'], true)) {
            return ApiResponse::error("Cannot cancel an appointment that is already {$currentStatus}.", [], 422, 'INVALID_TRANSITION');
        }

        $reason = $request->input('reason', 'Customer requested cancellation');
        $this->bookingService->transitionStatus($appointment, AppointmentStatus::cancelled->value, $request->user(), $reason);

        return ApiResponse::success(new AppointmentResource($appointment->load(['service.category', 'barber.user', 'serviceZone'])), 'Appointment cancelled successfully.');
    }

    public function updateStatus(UpdateAppointmentStatusRequest $request, int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('manageForBarber', $appointment);

        $dbStatus = match ($request->status) {
            'checked_in', 'in_progress' => AppointmentStatus::confirmed->value,
            'completed' => AppointmentStatus::completed->value,
            'no_show' => AppointmentStatus::no_show->value,
            'cancelled' => AppointmentStatus::cancelled->value,
            default => AppointmentStatus::confirmed->value,
        };

        $reason = $request->reason ?? "Status updated to {$request->status}";
        $this->bookingService->transitionStatus($appointment, $dbStatus, $request->user(), $reason);
        $appointment->load(['service.category', 'barber.user', 'serviceZone']);

        return ApiResponse::success(new AppointmentResource($appointment), "Appointment status updated to {$request->status}.");
    }

    public function storeWalkIn(StoreWalkInRequest $request): JsonResponse
    {
        $barber = $request->user()->barber;
        $barberId = $barber?->id ?? ($request->barber_id ?? (Barber::value('id') ?? 1));

        try {
            $appointment = $this->createWalkIn->execute(
                $request->user(),
                Barber::findOrFail((int) $barberId),
                BookingData::fromRequest($request)
            );

            return ApiResponse::success(new AppointmentResource($appointment), 'Walk-in client booked successfully.', 201);
        } catch (BookingSlotUnavailableException $e) {
            return $e->render($request);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 422, 'BOOKING_FAILED');
        }
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('manageForBarber', $appointment);

        $reason = $request->input('reason', 'Appointment approved');
        $this->bookingService->transitionStatus($appointment, AppointmentStatus::confirmed->value, $request->user(), $reason);

        return ApiResponse::success(new AppointmentResource($appointment->load(['service.category', 'barber.user', 'serviceZone'])), 'Appointment approved successfully.');
    }

    public function forceApprove(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('manageForBarber', $appointment);

        $reason = $request->input('reason', 'Appointment force-approved');
        $this->bookingService->transitionStatus($appointment, AppointmentStatus::confirmed->value, $request->user(), $reason);

        return ApiResponse::success(new AppointmentResource($appointment->load(['service.category', 'barber.user', 'serviceZone'])), 'Appointment force-approved successfully.');
    }
}
