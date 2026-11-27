<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalogue\Services\DirectionsService;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopLocationApiController
{
    public function show(Request $request, DirectionsService $service, ?int $id = null): JsonResponse
    {
        $branchId = $id ?? ($request->filled('branch_id') ? $request->integer('branch_id') : null);

        return ApiResponse::success($service->getShopLocation($branchId), 'Shop location retrieved.');
    }

    public function directions(Request $request, DirectionsService $service): JsonResponse
    {
        $validated = $request->validate([
            'from_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'from_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'from_address' => ['nullable', 'string', 'max:300'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        return ApiResponse::success($service->getDirections(
            fromLat: isset($validated['from_lat']) ? (float) $validated['from_lat'] : null,
            fromLng: isset($validated['from_lng']) ? (float) $validated['from_lng'] : null,
            fromAddress: $validated['from_address'] ?? null,
            branchId: isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
        ), 'Directions computed.');
    }

    public function geocode(Request $request, DirectionsService $service): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:300'],
        ]);

        return ApiResponse::success(
            $service->geocodeSearch((string) $validated['q']),
            'Locations resolved.'
        );
    }

    public function reverseGeocode(Request $request, DirectionsService $service): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        return ApiResponse::success(
            $service->reverseGeocode((float) $validated['lat'], (float) $validated['lng']),
            'Address resolved.'
        );
    }
}
