<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Responses\ApiResponse;
use App\Models\FeatureFlag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeatureFlagApiController
{
    /**
     * Public endpoint for clients to fetch active flags for their platform.
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $platform = $request->query('platform')
            ?? ($request->header('X-Client-Type') === 'mobile' ? 'app' : 'web');

        $platform = strtolower((string) $platform);

        $flags = FeatureFlag::query()
            ->where('is_active', true)
            ->get();

        $activeMap = [];
        foreach ($flags as $flag) {
            $activeMap[$flag->key] = $flag->isEnabledForPlatform($platform);
        }

        return ApiResponse::success([
            'platform' => $platform,
            'flags' => $activeMap,
        ], 'Feature flags retrieved successfully.');
    }

    /**
     * Admin: list all feature flags.
     */
    public function index(): JsonResponse
    {
        $flags = FeatureFlag::query()
            ->orderByDesc('created_at')
            ->get();

        return ApiResponse::success($flags, 'Feature flags retrieved successfully.');
    }

    /**
     * Admin: create a feature flag.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100|unique:feature_flags,key',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'enabled_for' => 'required|array|min:1',
            'enabled_for.*' => 'string|in:web,app',
            'rollout_percentage' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $flag = FeatureFlag::create([
            'key' => $validated['key'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'enabled_for' => array_values(array_unique($validated['enabled_for'])),
            'rollout_percentage' => $validated['rollout_percentage'] ?? 100,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return ApiResponse::success($flag, 'Feature flag created successfully.', 201);
    }

    /**
     * Admin: update a feature flag.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $flag = FeatureFlag::findOrFail($id);

        $validated = $request->validate([
            'key' => "required|string|max:100|unique:feature_flags,key,{$id}",
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'enabled_for' => 'required|array|min:1',
            'enabled_for.*' => 'string|in:web,app',
            'rollout_percentage' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $flag->update([
            'key' => $validated['key'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'enabled_for' => array_values(array_unique($validated['enabled_for'])),
            'rollout_percentage' => $validated['rollout_percentage'] ?? 100,
            'is_active' => $validated['is_active'] ?? $flag->is_active,
        ]);

        return ApiResponse::success($flag, 'Feature flag updated successfully.');
    }

    /**
     * Admin: toggle active state.
     */
    public function toggle(int $id): JsonResponse
    {
        $flag = FeatureFlag::findOrFail($id);
        $flag->is_active = ! $flag->is_active;
        $flag->save();

        return ApiResponse::success($flag, "Feature flag {$flag->key} is now ".($flag->is_active ? 'active' : 'inactive').'.');
    }

    /**
     * Admin: delete a feature flag.
     */
    public function destroy(int $id): JsonResponse
    {
        $flag = FeatureFlag::findOrFail($id);
        $flag->delete();

        return ApiResponse::success(null, 'Feature flag deleted successfully.');
    }
}
