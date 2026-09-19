<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Admin\Services\DashboardService;
use App\Domain\Content\Actions\UpdateSettings;
use App\Domain\Content\Services\SettingsService;
use App\Domain\Identity\Actions\ManageUsers;
use App\Domain\Shared\Actions\SecureImageUpload;
use App\Http\Requests\Api\V1\SuperAdmin\StoreUserRequest;
use App\Http\Requests\Api\V1\SuperAdmin\UpdateSettingsRequest;
use App\Http\Requests\Api\V1\SuperAdmin\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class SuperAdminApiController
{
    public function dashboard(DashboardService $dashboardService): JsonResponse
    {
        return ApiResponse::success($dashboardService->dashboard(), 'Dashboard loaded');
    }

    public function users(ManageUsers $manageUsers): JsonResponse
    {
        return ApiResponse::paginated($manageUsers->listUsers(), 'Users loaded');
    }

    public function storeUser(StoreUserRequest $request, ManageUsers $manageUsers): JsonResponse
    {
        return ApiResponse::success(new UserResource($manageUsers->storeUser($request->validated())), 'User created', 201);
    }

    public function updateUser(UpdateUserRequest $request, User $user, ManageUsers $manageUsers): JsonResponse
    {
        return ApiResponse::success(new UserResource($manageUsers->updateUser($user, $request->validated())), 'User updated');
    }

    public function activateUser(User $user, ManageUsers $manageUsers): JsonResponse
    {
        return ApiResponse::success(new UserResource($manageUsers->activateUser($user)), 'User activated');
    }

    public function deactivateUser(User $user, ManageUsers $manageUsers): JsonResponse
    {
        return ApiResponse::success(new UserResource($manageUsers->deactivateUser($user)), 'User deactivated');
    }

    public function deleteUser(User $user, ManageUsers $manageUsers): JsonResponse
    {
        $manageUsers->deleteUser($user);

        return ApiResponse::success(null, 'User deleted');
    }

    public function settings(SettingsService $settingsService): JsonResponse
    {
        return ApiResponse::success($settingsService->settings(), 'Settings loaded');
    }

    public function updateSettings(UpdateSettingsRequest $request, UpdateSettings $action, SettingsService $settingsService): JsonResponse
    {
        $settings = $request->validated('settings', []);

        if ($request->hasFile('hero_image')) {
            $path = (new SecureImageUpload)->execute($request->file('hero_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'hero_image',
                'value' => '/storage/'.$path,
                'group' => 'hero',
            ];
        }

        if ($request->hasFile('splash_image')) {
            $path = (new SecureImageUpload)->execute($request->file('splash_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'splash_background_image',
                'value' => '/storage/'.$path,
                'group' => 'mobile',
            ];
        }

        if ($request->hasFile('onboarding_image')) {
            $path = (new SecureImageUpload)->execute($request->file('onboarding_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'onboarding_background_image',
                'value' => '/storage/'.$path,
                'group' => 'mobile',
            ];
        }

        if ($request->hasFile('login_image')) {
            $path = (new SecureImageUpload)->execute($request->file('login_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'login_background_image',
                'value' => '/storage/'.$path,
                'group' => 'mobile',
            ];
        }

        $action->execute(['settings' => $settings]);

        return ApiResponse::success($settingsService->settings(), 'Settings updated');
    }

    public function auditLogs(DashboardService $dashboardService): JsonResponse
    {
        return ApiResponse::paginated($dashboardService->auditLogs(), 'Audit logs loaded');
    }
}
