<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Admin\Services\DashboardService;
use App\Domain\Content\Actions\UpdateSettings;
use App\Domain\Content\Services\SettingsService;
use App\Domain\Identity\Actions\ManageUsers;
use App\Domain\Shared\Actions\SecureImageUpload;
use App\Domain\Shared\Services\AuditLogger;
use App\Http\Requests\Api\V1\SuperAdmin\StoreUserRequest;
use App\Http\Requests\Api\V1\SuperAdmin\UpdateSettingsRequest;
use App\Http\Requests\Api\V1\SuperAdmin\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuperAdminApiController
{
    public function dashboard(DashboardService $dashboardService): JsonResponse
    {
        return ApiResponse::success($dashboardService->dashboard(), 'Dashboard loaded');
    }

    public function users(ManageUsers $manageUsers): JsonResponse
    {
        $users = $manageUsers->listUsers();
        $users->setCollection($users->getCollection()->map(
            fn (User $user): User => $user
        ));

        return ApiResponse::paginated($users, 'Users loaded');
    }

    public function storeUser(StoreUserRequest $request, ManageUsers $manageUsers): JsonResponse
    {
        $user = $manageUsers->storeUser($request->validated());
        AuditLogger::log('user_created', 'SuperAdmin', $user, [], ['email' => $user->email, 'role' => $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role]);

        return ApiResponse::success(new UserResource($user), 'User created', 201);
    }

    public function updateUser(UpdateUserRequest $request, User $user, ManageUsers $manageUsers): JsonResponse
    {
        $updated = $manageUsers->updateUser($user, $request->validated());
        AuditLogger::log('user_updated', 'SuperAdmin', $updated, [], $request->validated());

        return ApiResponse::success(new UserResource($updated), 'User updated');
    }

    public function activateUser(User $user, ManageUsers $manageUsers): JsonResponse
    {
        $activated = $manageUsers->activateUser($user);
        AuditLogger::log('user_activated', 'SuperAdmin', $activated);

        return ApiResponse::success(new UserResource($activated), 'User activated');
    }

    public function deactivateUser(User $user, ManageUsers $manageUsers): JsonResponse
    {
        $deactivated = $manageUsers->deactivateUser($user);
        AuditLogger::log('user_deactivated', 'SuperAdmin', $deactivated);

        return ApiResponse::success(new UserResource($deactivated), 'User deactivated');
    }

    public function resetPassword(User $user, Request $request, ManageUsers $manageUsers): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $reset = $manageUsers->adminResetPassword($user, $validated['password']);
        AuditLogger::log('password_reset', 'SuperAdmin', $user);

        return ApiResponse::success(new UserResource($reset), 'User password reset successfully');
    }

    public function deleteUser(User $user, ManageUsers $manageUsers): JsonResponse
    {
        $userId = $user->id;
        $manageUsers->deleteUser($user);
        AuditLogger::log('user_deleted', 'SuperAdmin', null, [], [], 'User', $userId);

        return ApiResponse::success(null, 'User deleted');
    }

    public function settings(SettingsService $settingsService): JsonResponse
    {
        return ApiResponse::success($settingsService->settings(), 'Settings loaded');
    }

    public function updateSettings(UpdateSettingsRequest $request, UpdateSettings $action, SettingsService $settingsService): JsonResponse
    {
        $settings = $request->validated('settings', []);
        if (empty($settings)) {
            $input = $request->except(['hero_image', 'page_hero_image', 'page_hero_page', 'about_teaser_image', 'about_shop_image', 'splash_image', 'onboarding_image', 'login_image', 'logo', 'favicon']);
            if (! empty($input)) {
                $settings = $input;
            }
        }

        $uploader = new SecureImageUpload;
        $deferredSettings = [];

        $imageFieldMap = [
            'hero_image' => ['key' => 'hero_image', 'group' => 'hero'],
            'about_teaser_image' => ['key' => 'about_teaser_image', 'group' => 'about'],
            'about_shop_image' => ['key' => 'about_shop_image', 'group' => 'about'],
            'splash_image' => ['key' => 'splash_background_image', 'group' => 'mobile'],
            'onboarding_image' => ['key' => 'onboarding_background_image', 'group' => 'mobile'],
            'login_image' => ['key' => 'login_background_image', 'group' => 'mobile'],
            'logo' => ['key' => 'logo', 'group' => 'general'],
            'favicon' => ['key' => 'favicon', 'group' => 'general'],
        ];

        foreach ($imageFieldMap as $field => $meta) {
            if ($request->hasFile($field)) {
                $rawPath = $uploader->storeFast($request->file($field), 'uploads/settings');
                $settings[] = [
                    'key' => $meta['key'],
                    'value' => '/storage/'.$rawPath,
                    'group' => $meta['group'],
                ];
                $deferredSettings[$meta['key']] = $rawPath;
            }
        }

        if ($request->hasFile('page_hero_image')) {
            $page = $request->validated('page_hero_page');
            $rawPath = $uploader->storeFast($request->file('page_hero_image'), 'uploads/settings');
            $settingKey = $page === 'home' ? 'hero_image' : "page_hero_{$page}_image";
            $settings[] = [
                'key' => $settingKey,
                'value' => '/storage/'.$rawPath,
                'group' => 'hero',
            ];
            $deferredSettings[$settingKey] = $rawPath;
        }

        $action->execute(['settings' => $settings]);

        foreach ($deferredSettings as $settingKey => $rawPath) {
            $uploader->dispatchOptimization(
                rawPath: $rawPath,
                disk: 'public',
                modelClass: Setting::class,
                modelKey: 0,
                attribute: 'value',
                prefixStorage: true,
                actorUserId: $request->user()?->id,
                cacheEntity: 'settings',
                settingKey: $settingKey,
            );
        }
        AuditLogger::log('settings_updated', 'SuperAdmin', null, [], ['keys' => array_keys($settings)]);

        return ApiResponse::success($settingsService->settings(), 'Settings updated');
    }

    public function auditLogs(Request $request, DashboardService $dashboardService): JsonResponse
    {
        $logs = $dashboardService->auditLogs(
            $request->query('module'),
            $request->query('search')
        );

        return ApiResponse::paginated($logs, 'Audit logs loaded');
    }
}
