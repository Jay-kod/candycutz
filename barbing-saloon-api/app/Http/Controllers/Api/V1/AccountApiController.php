<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Identity\Services\CustomerService;
use App\Domain\Identity\Services\UsernameIdentityService;
use App\Domain\Shared\Actions\SecureImageUpload;
use App\Domain\Shared\Services\AuditLogger;
use App\Domain\Shared\Services\UserMediaUrl;
use App\Http\Resources\UserProfileResource;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function updateProfile(Request $request, UsernameIdentityService $usernameService): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'username' => ['nullable', 'string', 'min:3', 'max:30'],
            'avatar' => ['nullable', 'image', 'max:5120'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        $removeAvatar = $request->boolean('remove_avatar') || $request->input('remove_avatar') === '1' || $request->input('remove_avatar') === 'true';
        $rawAvatarPath = null;
        $changedFields = [];
        foreach (['name', 'email', 'phone', 'bio'] as $field) {
            if (array_key_exists($field, $validated) && (string) $user->{$field} !== (string) $validated[$field]) {
                $changedFields[] = $field;
            }
        }

        if ($removeAvatar) {
            $validated['avatar'] = null;
            if ($user->avatar !== null) {
                $changedFields[] = 'avatar';
            }
        } elseif ($request->hasFile('avatar')) {
            $rawAvatarPath = (new SecureImageUpload)->storeFast(
                $request->file('avatar'),
                'user-media/'.$user->id.'/avatar',
                'local'
            );
            $validated['avatar'] = $rawAvatarPath;
            $changedFields[] = 'avatar';
        } else {
            unset($validated['avatar']);
        }
        unset($validated['remove_avatar']);

        // Handle username update through domain service with cooldown enforcement
        if (! empty($validated['username']) && strtolower(trim($validated['username'])) !== strtolower((string) $user->username)) {
            $changedFields[] = 'username';
            try {
                $usernameService->updateUsername($user, $validated['username']);
            } catch (\Exception $e) {
                throw ValidationException::withMessages([
                    'username' => [$e->getMessage()],
                ]);
            }
        }
        unset($validated['username']);

        $validated['real_name'] = $validated['name'];

        $user->update($validated);

        if ($changedFields !== []) {
            AuditLogger::log('profile.updated', 'Account', $user, [], [
                'changed_fields' => array_values(array_unique($changedFields)),
            ]);
        }

        if ($rawAvatarPath !== null) {
            (new SecureImageUpload)->dispatchOptimization(
                rawPath: $rawAvatarPath,
                disk: 'local',
                modelClass: User::class,
                modelKey: $user->id,
                attribute: 'avatar',
                prefixStorage: false,
                actorUserId: $user->id,
            );
        }

        return ApiResponse::success(new UserProfileResource($user->refresh()->loadCount(['appointments', 'testimonials'])), 'Profile updated');
    }

    public function analytics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'range' => ['sometimes', 'string', 'in:7d,30d,month,all'],
        ]);

        return ApiResponse::success(
            $this->customerService->analytics($request->user(), $validated['range'] ?? '7d'),
            'Analytics loaded'
        );
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
                'status' => $a->status->value ?? $a->status,
                'verification_code' => $a->verification_code,
                'service_name' => $a->service?->name,
                'service_image' => $a->service?->image,
                'barber_name' => $a->barber?->user?->name,
                'barber_avatar' => app(UserMediaUrl::class)->for($a->barber?->user, $a->barber?->user?->avatar),
            ]);

        return ApiResponse::success($codes, 'My verification codes loaded');
    }

    public function uploadAvatar(Request $request, SecureImageUpload $imageUpload): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        $path = $imageUpload->storeFast(
            $request->file('avatar'),
            'user-media/'.$user->id.'/avatar',
            'local'
        );

        $user->update(['avatar' => $path]);

        $imageUpload->dispatchOptimization(
            rawPath: $path,
            disk: 'local',
            modelClass: User::class,
            modelKey: $user->id,
            attribute: 'avatar',
            prefixStorage: false,
            actorUserId: $user->id,
        );

        return ApiResponse::success(
            new UserProfileResource($user->refresh()->loadCount(['appointments', 'testimonials'])),
            'Avatar uploaded successfully'
        );
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['avatar' => null]);

        return ApiResponse::success(
            new UserProfileResource($user->fresh()->loadCount(['appointments', 'testimonials'])),
            'Avatar removed successfully'
        );
    }
}
