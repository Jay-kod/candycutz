<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Shared\Enums\AppointmentStatus;
use App\Domain\Shared\Actions\SecureImageUpload;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Modules\Customer\Resources\UserProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountApiController
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        return ApiResponse::success($this->customerService->dashboard($request->user()), 'Customer dashboard loaded');
    }

    public function profile(Request $request): JsonResponse
    {
        return ApiResponse::success([
            'profile' => new UserProfileResource($request->user()->loadCount(['appointments', 'testimonials'])),
            'history' => $this->customerService->profile($request->user()),
        ], 'Profile loaded');
    }

    public function updateProfile(Request $request): JsonResponse
    {
        // Ported from UpdateProfileRequest in Customer module
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        
        if ($request->hasFile('avatar')) {
            $path = (new SecureImageUpload())->execute($request->file('avatar'), 'uploads/avatars');
            $validated['avatar'] = '/storage/' . $path;
        }

        $user->update($validated);

        return ApiResponse::success(new UserProfileResource($user->loadCount(['appointments', 'testimonials'])), 'Profile updated');
    }

    public function analytics(Request $request): JsonResponse
    {
        $user = $request->user();
        $totalSpent = Appointment::query()
            ->where('customer_id', $user->id)
            ->where('status', AppointmentStatus::completed->value)
            ->sum('total_price');

        $monthlyBookings = Appointment::query()
            ->where('customer_id', $user->id)
            ->where('appointment_date', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw('MONTHNAME(appointment_date) as month, COUNT(*) as bookings, SUM(total_price) as spent')
            ->groupBy('month')
            ->get();

        return ApiResponse::success([
            'total_spent' => (float) $totalSpent,
            'monthly' => $monthlyBookings,
        ], 'Analytics loaded');
    }

    public function myCodes(Request $request): JsonResponse
    {
        $user = $request->user();
        $codes = Appointment::query()
            ->with(['service', 'barber.user'])
            ->where('customer_id', $user->id)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'appointment_date' => $a->appointment_date,
                'appointment_time' => $a->appointment_time,
                'status' => $a->status?->value ?? $a->status,
                'verification_code' => $a->verification_code,
                'service_name' => $a->service?->name,
                'service_image' => $a->service?->image,
                'barber_name' => $a->barber?->user?->name,
                'barber_avatar' => $a->barber?->user?->avatar,
            ]);

        return ApiResponse::success($codes, 'My verification codes loaded');
    }
}
