<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Identity\Actions\CreateBarber;
use App\Domain\Identity\Actions\DeleteBarber;
use App\Domain\Identity\Actions\GetBarberDashboard;
use App\Domain\Identity\Actions\GetBarberSchedule;
use App\Domain\Identity\Actions\StoreBlockedPeriod;
use App\Domain\Identity\Actions\UpdateBarber;
use App\Domain\Identity\Actions\UpdateBarberAccount;
use App\Domain\Identity\Actions\UpdateBarberSchedule;
use App\Domain\Identity\Actions\UpdateBarberStatus;
use App\Domain\Shared\Actions\SecureImageUpload;
use App\Domain\Shared\Enums\AppointmentStatus;
use App\Http\Resources\BarberResource;
use App\Domain\Identity\Services\UsernameIdentityService;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BlockedPeriod;
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

    public function dashboard(Request $request, GetBarberDashboard $action): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;
        if (! $barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $data = $action->execute($barber);

        return ApiResponse::success($data, 'Barber dashboard loaded');
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

    public function updateAccount(Request $request, UpdateBarberAccount $action, SecureImageUpload $imageUpload): JsonResponse
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
            'specialties' => 'sometimes|array',
            'specialties.*' => 'string|max:50',
            'experience_years' => 'sometimes|integer|min:0|max:80',
            'instagram_url' => 'sometimes|string|nullable',
            'avatar' => 'sometimes|image|max:5120',
            'cover_image' => 'sometimes|image|max:5120',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = '/storage/'.$imageUpload->execute($request->file('avatar'), 'uploads/avatars');
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = '/storage/'.$imageUpload->execute($request->file('cover_image'), 'uploads/covers');
        }

        $action->execute($user, $data);

        return ApiResponse::success([
            'user' => $user->refresh(),
            'barber' => $barber ? new BarberResource($barber->refresh()) : null,
        ], 'Account updated');
    }

    public function updateUsername(Request $request, UsernameIdentityService $service): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:30'],
        ]);

        try {
            $user = $service->updateUsername($request->user(), $validated['username']);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), [], 422, 'USERNAME_UPDATE_FAILED');
        }

        return ApiResponse::success($user->refresh(), 'Username updated');
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

    public function updateMyStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if (! $barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $this->authorize('update', $barber);

        $status = (string) $request->input('status', 'active');
        $isAvailable = $request->boolean('is_available', in_array($status, ['active', 'free'], true));
        $chairStatus = match ($status) {
            'active', 'free' => 'free',
            'on_leave', 'offline' => 'offline',
            'suspended' => 'offline',
            default => 'busy',
        };

        $barber->update([
            'is_available' => $isAvailable,
            'chair_status' => $chairStatus,
        ]);

        return ApiResponse::success([
            'status' => $isAvailable ? 'active' : 'on_leave',
            'is_available' => (bool) $barber->is_available,
            'chair_status' => $barber->chair_status,
        ], 'Barber status updated');
    }

    public function schedule(Request $request, GetBarberSchedule $action): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if (! $barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $schedule = $action->execute($barber);

        return ApiResponse::success($schedule, 'Barber schedule retrieved');
    }

    public function updateSchedule(Request $request, UpdateBarberSchedule $updateAction, GetBarberSchedule $getAction): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if (! $barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $this->authorize('update', $barber);

        $hours = $request->input('schedule', $request->input('working_hours', []));
        if (is_array($hours)) {
            $updateAction->execute($barber, $hours);
        }

        return ApiResponse::success($getAction->execute($barber), 'Barber schedule updated');
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

    public function storeBlockedPeriod(Request $request, StoreBlockedPeriod $action): JsonResponse
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

        $period = $action->execute($barber, $user->id, $validated);

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
