<?php

declare(strict_types=1);

namespace App\Core\Http\Controllers\Api\V1;

use App\Core\Http\Response\ApiResponse;
use App\Models\ServiceZone;
use Illuminate\Http\JsonResponse;

class ServiceZoneApiController
{
    public function index(): JsonResponse
    {
        $zones = ServiceZone::query()
            ->where('is_active', true)
            ->get();

        $data = $zones->map(fn (ServiceZone $zone) => [
            'id' => $zone->id,
            'name' => $zone->name,
            'code' => $zone->code ?? \Illuminate\Support\Str::slug($zone->name),
            'description' => $zone->description ?? "Delivery coverage in {$zone->name}, Keffi",
            'surcharge' => (float) ($zone->base_travel_fee ?? 1500.0),
            'base_travel_fee' => (float) ($zone->base_travel_fee ?? 1500.0),
            'per_km_fee' => (float) ($zone->per_km_fee ?? 150.0),
            'min_order_amount' => 5000.0,
            'estimated_travel_minutes' => 20,
            'is_active' => (bool) $zone->is_active,
        ]);

        return ApiResponse::success($data, 'Service zones retrieved successfully');
    }
}
