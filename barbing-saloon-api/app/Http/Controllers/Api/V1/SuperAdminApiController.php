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

        if ($request->hasFile('hero_image')) {
            $path = (new SecureImageUpload)->execute($request->file('hero_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'hero_image',
                'value' => '/storage/'.$path,
                'group' => 'hero',
            ];
        }

        if ($request->hasFile('page_hero_image')) {
            $page = $request->validated('page_hero_page');
            $path = (new SecureImageUpload)->execute($request->file('page_hero_image'), 'uploads/settings');
            $settings[] = [
                'key' => $page === 'home' ? 'hero_image' : "page_hero_{$page}_image",
                'value' => '/storage/'.$path,
                'group' => 'hero',
            ];
        }

        if ($request->hasFile('about_teaser_image')) {
            $path = (new SecureImageUpload)->execute($request->file('about_teaser_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'about_teaser_image',
                'value' => '/storage/'.$path,
                'group' => 'about',
            ];
        }

        if ($request->hasFile('about_shop_image')) {
            $path = (new SecureImageUpload)->execute($request->file('about_shop_image'), 'uploads/settings');
            $settings[] = [
                'key' => 'about_shop_image',
                'value' => '/storage/'.$path,
                'group' => 'about',
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

        if ($request->hasFile('logo')) {
            $path = (new SecureImageUpload)->execute($request->file('logo'), 'uploads/settings');
            $settings[] = [
                'key' => 'logo',
                'value' => '/storage/'.$path,
                'group' => 'general',
            ];
        }

        if ($request->hasFile('favicon')) {
            $path = (new SecureImageUpload)->execute($request->file('favicon'), 'uploads/settings');
            $settings[] = [
                'key' => 'favicon',
                'value' => '/storage/'.$path,
                'group' => 'general',
            ];
        }

        $action->execute(['settings' => $settings]);
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
