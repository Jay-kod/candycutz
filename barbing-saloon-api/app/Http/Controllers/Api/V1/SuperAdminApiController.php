<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Admin\Services\DashboardService;
use App\Domain\Content\Actions\UpdateSettings;
use App\Domain\Content\Services\SettingsService;
use App\Domain\Identity\Actions\ManageUsers;
use App\Http\Requests\Api\V1\SuperAdmin\StoreUserRequest;
use App\Http\Requests\Api\V1\SuperAdmin\UpdateSettingsRequest;
use App\Http\Requests\Api\V1\SuperAdmin\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;

class SuperAdminApiController
{
    public function dashboard(DashboardService $dashboardService)
    {
        return ApiResponse::success($dashboardService->dashboard(), 'Dashboard loaded');
    }

    public function users(ManageUsers $manageUsers)
    {
        return ApiResponse::paginated($manageUsers->listUsers(), 'Users loaded');
    }

    public function storeUser(StoreUserRequest $request, ManageUsers $manageUsers)
    {
        return ApiResponse::success(new UserResource($manageUsers->storeUser($request->validated())), 'User created', 201);
    }

    public function updateUser(UpdateUserRequest $request, User $user, ManageUsers $manageUsers)
    {
        return ApiResponse::success(new UserResource($manageUsers->updateUser($user, $request->validated())), 'User updated');
    }

    public function activateUser(User $user, ManageUsers $manageUsers)
    {
        return ApiResponse::success(new UserResource($manageUsers->activateUser($user)), 'User activated');
    }

    public function deactivateUser(User $user, ManageUsers $manageUsers)
    {
        return ApiResponse::success(new UserResource($manageUsers->deactivateUser($user)), 'User deactivated');
    }

    public function deleteUser(User $user, ManageUsers $manageUsers)
    {
        $manageUsers->deleteUser($user);

        return ApiResponse::success(null, 'User deleted');
    }

    public function settings(SettingsService $settingsService)
    {
        return ApiResponse::success($settingsService->settings(), 'Settings loaded');
    }

    public function updateSettings(UpdateSettingsRequest $request, UpdateSettings $action, SettingsService $settingsService)
    {
        $action->execute($request->validated());

        return ApiResponse::success($settingsService->settings(), 'Settings updated');
    }

    public function auditLogs(DashboardService $dashboardService)
    {
        return ApiResponse::paginated($dashboardService->auditLogs(), 'Audit logs loaded');
    }
}
