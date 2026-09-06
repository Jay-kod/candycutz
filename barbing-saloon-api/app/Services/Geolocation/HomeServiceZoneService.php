<?php

declare(strict_types=1);

namespace App\Services\Geolocation;

use App\Models\Branch;
use App\Models\ServiceZone;
use Exception;

class HomeServiceZoneService
{
    /**
     * Compute dynamic travel fee between a customer coordinate and branch hub.
     */
    public function calculateTravelFee(
        ServiceZone $zone,
        float $customerLat,
        float $customerLng,
        ?Branch $branch = null
    ): array {
        $branch = $branch ?: $zone->branch;
        $branchLat = (float) ($branch->latitude ?? 8.84710000);
        $branchLng = (float) ($branch->longitude ?? 7.87360000);

        $distanceKm = $this->haversineDistance($customerLat, $customerLng, $branchLat, $branchLng);

        if ($distanceKm > $zone->radius_km) {
            throw new Exception("Destination is {$distanceKm} km away, which exceeds the {$zone->name} limit of {$zone->radius_km} km.");
        }

        $fee = (float) $zone->base_travel_fee + ($distanceKm * (float) $zone->per_km_fee);

        return [
            'zone_id' => $zone->id,
            'zone_name' => $zone->name,
            'distance_km' => round($distanceKm, 2),
            'base_travel_fee' => (float) $zone->base_travel_fee,
            'per_km_fee' => (float) $zone->per_km_fee,
            'total_travel_fee' => round($fee, 2),
        ];
    }

    /**
     * Great-circle distance between two coordinate pairs in kilometers.
     */
    protected function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}
