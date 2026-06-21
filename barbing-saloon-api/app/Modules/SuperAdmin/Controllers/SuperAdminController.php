<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Core\Http\Response\ApiResponse;
use App\Models\User;
use App\Modules\SuperAdmin\Requests\StoreUserRequest;
use App\Modules\SuperAdmin\Requests\UpdateUserRequest;
use App\Modules\SuperAdmin\Requests\UpdateSettingsRequest;
use App\Modules\SuperAdmin\Resources\UserResource;
use App\Modules\SuperAdmin\Resources\AuditLogResource;
use App\Modules\SuperAdmin\Services\SuperAdminService;

class SuperAdminController
{
    public function __construct(protected SuperAdminService $superAdminService)
    {
    }

    public function dashboard()
    {
        return ApiResponse::success($this->superAdminService->dashboard(), 'Dashboard loaded');
    }

    public function users()
    {
        return ApiResponse::paginated($this->superAdminService->users(), 'Users loaded');
    }

    public function storeUser(StoreUserRequest $request)
    {
        return ApiResponse::success(new UserResource($this->superAdminService->storeUser($request->validated())), 'User created', 201);
    }

    public function updateUser(UpdateUserRequest $request, User $user)
    {
        return ApiResponse::success(new UserResource($this->superAdminService->updateUser($user, $request->validated())), 'User updated');
    }

    public function activateUser(User $user)
    {
        return ApiResponse::success(new UserResource($this->superAdminService->activateUser($user)), 'User activated');
    }

    public function deactivateUser(User $user)
    {
        return ApiResponse::success(new UserResource($this->superAdminService->deactivateUser($user)), 'User deactivated');
    }

    public function deleteUser(User $user)
    {
        $this->superAdminService->deleteUser($user);

        return ApiResponse::success(null, 'User deleted');
    }

    public function settings()
    {
        return ApiResponse::success($this->superAdminService->settings(), 'Settings loaded');
    }

    public function updateSettings(UpdateSettingsRequest $request)
    {
        return ApiResponse::success($this->superAdminService->updateSettings($request->validated()), 'Settings updated');
    }

    public function auditLogs()
    {
        return ApiResponse::paginated($this->superAdminService->auditLogs(), 'Audit logs loaded');
    }
}
