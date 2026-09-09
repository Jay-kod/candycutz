<?php

declare(strict_types=1);

namespace App\Core\Http\Controllers\Api\V1;

use App\Core\Http\Response\ApiResponse;
use App\Models\Barber;
use App\Models\BlockedPeriod;
use App\Models\WorkingHour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarberApiController
{
    public function index(): JsonResponse
    {
        $barbers = Barber::query()
            ->with(['user', 'workingHours'])
            ->where('is_available', true)
            ->orderBy('display_order')
            ->orderByDesc('rating')
            ->get();

        $data = $barbers->map(fn (Barber $barber) => $this->formatBarber($barber));

        return ApiResponse::success($data, 'Barbers retrieved successfully');
    }

    public function show(int $id): JsonResponse
    {
        $barber = Barber::query()
            ->with(['user', 'workingHours', 'testimonials.customer'])
            ->find($id);

        if (!$barber) {
            return ApiResponse::error("Barber #{$id} not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        return ApiResponse::success($this->formatBarber($barber), 'Barber details retrieved');
    }

    public function updateChairStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if (!$barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        if ($request->has('chair_status') && !$request->has('status')) {
            $request->merge(['status' => $request->input('chair_status')]);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:free,busy,break,offline'],
        ]);

        $barber->update([
            'chair_status' => $validated['status'],
            'is_available' => $validated['status'] !== 'offline',
        ]);

        return ApiResponse::success($this->formatBarber($barber->refresh()), 'Chair status updated successfully');
    }

    public function schedule(Request $request): JsonResponse
    {
        $user = $request->user();
        $barber = $user->barber;

        if (!$barber) {
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

        if (!$barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $hours = $request->input('schedule', $request->input('working_hours', []));
        if (is_array($hours)) {
            foreach ($hours as $item) {
                if (!isset($item['day_of_week'])) {
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

        if (!$barber) {
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

        if (!$barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

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

        if (!$barber) {
            return ApiResponse::error('Authenticated user is not an active barber.', [], 403, 'FORBIDDEN_ROLE');
        }

        $period = BlockedPeriod::where('barber_id', $barber->id)->where('id', $id)->first();
        if (!$period) {
            return ApiResponse::error("Blocked period #{$id} not found.", [], 404, 'RESOURCE_NOT_FOUND');
        }

        $period->delete();

        return ApiResponse::success(null, 'Blocked period deleted successfully');
    }

    protected function formatBarber(Barber $barber): array
    {
        $u = $barber->user;
        $specialties = is_array($barber->specialties) ? $barber->specialties : (json_decode($barber->specialties ?? '[]', true) ?: []);

        return [
            'id' => $barber->id,
            'user_id' => $barber->user_id,
            'name' => $u?->name ?? 'Master Barber',
            'real_name' => $u?->real_name ?? $u?->name ?? 'Master Barber',
            'username' => $u?->username ?? 'barber',
            'email' => $u?->email ?? '',
            'phone' => $u?->phone ?? '',
            'avatar' => $u?->avatar,
            'avatar_url' => $u?->avatar ? (str_starts_with($u->avatar, 'http') ? $u->avatar : url('storage/' . ltrim($u->avatar, '/'))) : null,
            'rating' => (float) ($barber->rating ?? 5.0),
            'total_reviews' => (int) ($barber->testimonials()->count() ?: 12),
            'experience_years' => (int) ($barber->experience_years ?? $barber->years_experience ?? 5),
            'chair_status' => $barber->chair_status ?? 'free',
            'specialties' => $specialties,
            'bio' => $barber->bio ?? 'Expert master barber with precision razor craft.',
            'is_available' => (bool) $barber->is_available,
            'is_active' => (bool) ($u?->is_active ?? true),
        ];
    }
}
