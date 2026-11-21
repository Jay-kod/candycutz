<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Content\Services\AnimationSettingsService;
use App\Http\Requests\Api\V1\Admin\UpdateAnimationSettingsRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AnimationApiController
{
    /**
     * Get animation configuration for the admin control panel.
     */
    public function index(AnimationSettingsService $service): JsonResponse
    {
        Gate::authorize('viewAny', Setting::class);

        return ApiResponse::success([
            'settings' => $service->getResolvedSettings(),
            'allowed_types' => AnimationSettingsService::ALLOWED_TYPES,
            'allowed_override_types' => AnimationSettingsService::ALLOWED_OVERRIDE_TYPES,
            'allowed_easings' => AnimationSettingsService::ALLOWED_EASINGS,
            'defaults' => AnimationSettingsService::DEFAULT_SETTINGS,
        ], 'Animation settings loaded');
    }

    /**
     * Update animation settings.
     */
    public function update(UpdateAnimationSettingsRequest $request, AnimationSettingsService $service): JsonResponse
    {
        $updated = $service->update($request->validated(), $request->user());

        return ApiResponse::success($updated, 'Animation settings updated successfully');
    }

    /**
     * Reset animation configuration to factory defaults.
     */
    public function reset(Request $request, AnimationSettingsService $service): JsonResponse
    {
        Gate::authorize('update', Setting::class);

        $defaults = $service->resetToDefaults($request->user());

        return ApiResponse::success($defaults, 'Animation settings reset to defaults successfully');
    }
}
