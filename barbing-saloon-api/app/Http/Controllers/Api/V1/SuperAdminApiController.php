<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Http\Requests\Api\V1\SuperAdmin\StoreUserRequest;
use App\Http\Requests\Api\V1\SuperAdmin\UpdateUserRequest;
use App\Http\Requests\Api\V1\SuperAdmin\UpdateSettingsRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Resources\Api\V1\AuditLogResource;
use App\Services\SuperAdminService;

class SuperAdminApiController
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
