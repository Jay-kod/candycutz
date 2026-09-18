<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Identity\Actions\CreateBarber;
use App\Domain\Identity\Actions\DeleteBarber;
use App\Domain\Identity\Actions\UpdateBarber;
use App\Domain\Identity\Actions\UpdateBarberStatus;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Http\Resources\BarberResource;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BlockedPeriod;
use App\Models\WorkingHour;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarberApiController
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        $barbers = Barber::query()
            ->with(['user', 'workingHours'])
            ->where('is_available', true)
            ->orderBy('display_order')
            ->orderByDesc('rating')
            ->get();

        return ApiResponse::success(BarberResource::collection($barbers), 'Barbers retrieved successfully');
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;
        if (! $barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $today = today()->toDateString();
        $todayAppointments = Appointment::with(['service', 'customer'])
            ->where('barber_id', $barber->id)
            ->where('appointment_date', $today)
            ->orderBy('appointment_time')
            ->get();

        $upcomingCount = Appointment::where('barber_id', $barber->id)
            ->where('appointment_date', '>=', $today)
            ->whereIn('status', [AppointmentStatus::pending->value, AppointmentStatus::confirmed->value])
            ->count();

        $completedToday = Appointment::where('barber_id', $barber->id)
            ->where('appointment_date', $today)
            ->where('status', AppointmentStatus::completed->value)
            ->count();

        return ApiResponse::success([
            'today_appointments' => $todayAppointments,
            'stats' => [
                'upcoming' => $upcomingCount,
                'completed_today' => $completedToday,
            ],
        ], 'Barber dashboard loaded');
    }

    public function analytics(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if (! $barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $completed = Appointment::where('barber_id', $barber->id)->where('status', AppointmentStatus::completed->value)->count();
        $revenue = Appointment::where('barber_id', $barber->id)->where('status', AppointmentStatus::completed->value)->sum('total_price');

        return ApiResponse::success([
            'completed_count' => $completed,
            'revenue' => (float) $revenue,
        ], 'Barber analytics loaded');
    }

    public function account(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        return ApiResponse::success([
            'user' => $user,
            'barber' => $barber ? new BarberResource($barber) : null,
        ], 'Account details loaded');
    }

    public function updateAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if ($barber) {
            $this->authorize('update', $barber);
        }

        $data = $request->validate([
            'name' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'bio' => 'sometimes|string|nullable',
            'specialties' => 'sometimes|string|nullable',
            'instagram_url' => 'sometimes|string|nullable',
        ]);

        $userData = array_intersect_key($data, array_flip(['name', 'phone']));
        if (! empty($userData)) {
            $user->update($userData);
        }

        $barberData = array_intersect_key($data, array_flip(['bio', 'specialties', 'instagram_url']));
        if (! empty($barberData) && $barber) {
            $barber->update($barberData);
        }

        return ApiResponse::success([
            'user' => $user->refresh(),
            'barber' => $barber ? new BarberResource($barber->refresh()) : null,
        ], 'Account updated');
    }

    public function show(int $id): JsonResponse
    {
        $barber = Barber::query()
            ->with(['user', 'workingHours', 'testimonials.customer'])
            ->find($id);

        if (! $barber) {
            return ApiResponse::error("Barber #{$id} not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        return ApiResponse::success(new BarberResource($barber), 'Barber details retrieved');
    }

    public function updateChairStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if (! $barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $this->authorize('update', $barber);

        if ($request->has('chair_status') && ! $request->has('status')) {
            $request->merge(['status' => $request->input('chair_status')]);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:free,busy,break,offline'],
        ]);

        $barber->update([
            'chair_status' => $validated['status'],
            'is_available' => $validated['status'] !== 'offline',
        ]);

        return ApiResponse::success(new BarberResource($barber->refresh()), 'Chair status updated successfully');
    }

    public function schedule(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if (! $barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $hours = WorkingHour::where('barber_id', $barber->id)
            ->orderBy('day_of_week')
            ->get();

        // Ensure all 7 days (0 to 6) are represented
        $schedule = [];
        $existing = $hours->keyBy('day_of_week');
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        for ($d = 0; $d < 7; $d++) {
            if ($existing->has($d)) {
                $row = $existing->get($d);
                $schedule[] = [
                    'id' => $row->id,
                    'day_of_week' => $d,
                    'day_name' => $dayNames[$d],
                    'open_time' => substr((string) $row->open_time, 0, 5),
                    'close_time' => substr((string) $row->close_time, 0, 5),
                    'start_time' => substr((string) $row->open_time, 0, 5),
                    'end_time' => substr((string) $row->close_time, 0, 5),
                    'is_closed' => (bool) $row->is_closed,
                    'is_off' => (bool) $row->is_closed,
                ];
            } else {
                $schedule[] = [
                    'id' => null,
                    'day_of_week' => $d,
                    'day_name' => $dayNames[$d],
                    'open_time' => '08:00',
                    'close_time' => '20:00',
                    'start_time' => '08:00',
                    'end_time' => '20:00',
                    'is_closed' => $d === 0,
                    'is_off' => $d === 0,
                ];
            }
        }

        return ApiResponse::success($schedule, 'Barber schedule retrieved');
    }

    public function updateSchedule(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if (! $barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $this->authorize('update', $barber);

        $hours = $request->input('schedule', $request->input('working_hours', []));
        if (is_array($hours)) {
            foreach ($hours as $item) {
                if (! isset($item['day_of_week'])) {
                    continue;
                }

                $open = $item['open_time'] ?? ($item['start_time'] ?? '08:00');
                $close = $item['close_time'] ?? ($item['end_time'] ?? '20:00');
                $isClosed = isset($item['is_closed'])
                    ? (bool) $item['is_closed']
                    : (isset($item['is_off']) ? (bool) $item['is_off'] : false);

                WorkingHour::updateOrCreate(
                    [
                        'barber_id' => $barber->id,
                        'day_of_week' => (int) $item['day_of_week'],
                    ],
                    [
                        'open_time' => $open,
                        'close_time' => $close,
                        'is_closed' => $isClosed,
                    ]
                );
            }
        }

        return $this->schedule($request);
    }

    public function blockedPeriods(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if (! $barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $periods = BlockedPeriod::where('barber_id', $barber->id)
            ->where('end_datetime', '>=', now()->startOfDay())
            ->orderBy('start_datetime')
            ->get();

        return ApiResponse::success($periods, 'Blocked periods retrieved');
    }

    public function storeBlockedPeriod(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if (! $barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $this->authorize('update', $barber);

        $validated = $request->validate([
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $period = BlockedPeriod::create([
            'barber_id' => $barber->id,
            'branch_id' => $barber->branch_id ?? 1,
            'start_datetime' => $validated['start_datetime'],
            'end_datetime' => $validated['end_datetime'],
            'reason' => $validated['reason'] ?? 'Personal Time Off',
            'created_by' => $user->id,
        ]);

        return ApiResponse::success($period, 'Time block created successfully', 201);
    }

    public function deleteBlockedPeriod(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if (! $barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $this->authorize('update', $barber);

        $period = BlockedPeriod::where('barber_id', $barber->id)->where('id', $id)->first();
        if (! $period) {
            return ApiResponse::error("Blocked period #{$id} not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        $period->delete();

        return ApiResponse::success(null, 'Blocked period deleted successfully');
    }

    // CRUD Methods from AdminApiController

    public function store(Request $request, CreateBarber $action): JsonResponse
    {
        $this->authorize('create', Barber::class);

        $data = $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email',
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string',
            'experience_years' => 'nullable|integer',
            'specialties' => 'nullable',
            'bio' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $barber = $action->execute($data);
        return ApiResponse::success(new BarberResource($barber), 'Barber created', 201);
    }

    public function update(Request $request, int $id, UpdateBarber $action): JsonResponse
    {
        $barber = Barber::findOrFail($id);
        $this->authorize('update', $barber);

        $data = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string',
            'experience_years' => 'nullable|integer',
            'specialties' => 'nullable',
            'bio' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $updatedBarber = $action->execute($id, $data);
        return ApiResponse::success(new BarberResource($updatedBarber), 'Barber updated');
    }

    public function updateStatus(Request $request, int $id, UpdateBarberStatus $action): JsonResponse
    {
        $barber = Barber::findOrFail($id);
        $this->authorize('update', $barber);

        $status = $request->input('status', 'active');
        $updatedBarber = $action->execute($id, $status);

        return ApiResponse::success(new BarberResource($updatedBarber), 'Barber status updated');
    }

    public function destroy(int $id, DeleteBarber $action): JsonResponse
    {
        $barber = Barber::findOrFail($id);
        $this->authorize('delete', $barber);

        $action->execute($id);
        return ApiResponse::success(null, 'Barber deleted');
    }
}
