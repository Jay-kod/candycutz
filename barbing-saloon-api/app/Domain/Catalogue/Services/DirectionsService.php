<?php

declare(strict_types=1);

namespace App\Domain\Catalogue\Services;

use App\Models\Branch;
use App\Models\Setting;
use App\Models\WorkingHour;
use Illuminate\Support\Facades\Cache;

class DirectionsService
{
    public const DEFAULT_NAME = 'CandyCutz Main Lounge';

    public const DEFAULT_ADDRESS = 'Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, BCG, Keffi 961101, Nasarawa, Nigeria';

    public const DEFAULT_LATITUDE = 8.8486;

    public const DEFAULT_LONGITUDE = 7.8736;

    /**
     * Resolve the authoritative locked shop destination metadata and navigation deep links.
     *
     * @return array<string, mixed>
     */
    public function getShopLocation(?int $branchId = null): array
    {
        if ($branchId === null) {
            return Cache::remember('catalogue.shop_location', 300, fn (): array => $this->resolveShopLocation(null));
        }

        return $this->resolveShopLocation($branchId);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveShopLocation(?int $branchId): array
    {
        $branch = $branchId
            ? Branch::whereKey($branchId)->first()
            : (Branch::where('is_active', true)->first() ?? Branch::first());

        $settings = Setting::whereIn('key', [
            'brand_name',
            'site_name',
            'address',
            'contact_address',
            'latitude',
            'longitude',
            'phone',
            'contact_phone',
            'whatsapp',
            'contact_hours',
            'map_link',
            'contact_map_link',
        ])->pluck('value', 'key');

        $name = $branch?->name ?: ($settings['brand_name'] ?? $settings['site_name'] ?? self::DEFAULT_NAME);
        $address = $branch?->address ?: ($settings['contact_address'] ?? $settings['address'] ?? self::DEFAULT_ADDRESS);

        $lat = $branch->latitude ?? (is_numeric($settings['latitude'] ?? null) ? (float) $settings['latitude'] : self::DEFAULT_LATITUDE);
        $lng = $branch->longitude ?? (is_numeric($settings['longitude'] ?? null) ? (float) $settings['longitude'] : self::DEFAULT_LONGITUDE);

        $phone = $branch?->phone ?: ($settings['contact_phone'] ?? $settings['phone'] ?? '+234 810 000 0000');
        $whatsapp = $settings['whatsapp'] ?? $phone;
        $mapLink = ($settings['contact_map_link'] ?? $settings['map_link'] ?? '') ?: "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";

        return [
            'branch_id' => $branch?->id,
            'name' => $name,
            'address' => $address,
            'latitude' => round((float) $lat, 6),
            'longitude' => round((float) $lng, 6),
            'locked' => true,
            'phone' => $phone,
            'whatsapp' => $whatsapp,
            'hours_summary' => $settings['contact_hours'] ?? 'Mon–Sat 8:00 AM – 8:00 PM',
            'working_hours' => $this->resolveWorkingHours(),
            'map_link' => $mapLink,
            'directions_links' => $this->buildDirectionLinks((float) $lat, (float) $lng),
        ];
    }

    /**
     * Build live directions links, distance, and travel estimates from an origin to the locked shop.
     *
     * @return array<string, mixed>
     */
    public function getDirections(
        ?float $fromLat = null,
        ?float $fromLng = null,
        ?string $fromAddress = null,
        ?int $branchId = null,
    ): array {
        $shop = $this->getShopLocation($branchId);
        $toLat = (float) $shop['latitude'];
        $toLng = (float) $shop['longitude'];

        $hasCoords = $fromLat !== null && $fromLng !== null;
        $distanceKm = $hasCoords
            ? round($this->haversineKm($fromLat, $fromLng, $toLat, $toLng), 2)
            : null;

        $driveMinutes = $distanceKm !== null ? max(2, (int) ceil(($distanceKm / 30.0) * 60)) : null;
        $walkMinutes = $distanceKm !== null ? max(3, (int) ceil(($distanceKm / 5.0) * 60)) : null;
        $bearing = $hasCoords ? $this->compassBearing($fromLat, $fromLng, $toLat, $toLng) : null;

        return [
            'from' => [
                'latitude' => $hasCoords ? round($fromLat, 6) : null,
                'longitude' => $hasCoords ? round($fromLng, 6) : null,
                'address' => $fromAddress ?: ($hasCoords ? 'Current GPS Location' : null),
            ],
            'to' => $shop,
            'estimated_distance_km' => $distanceKm,
            'estimated_drive_minutes' => $driveMinutes,
            'estimated_walk_minutes' => $walkMinutes,
            'compass_bearing' => $bearing,
            'directions_links' => $this->buildDirectionLinks($toLat, $toLng, $fromLat, $fromLng, $fromAddress),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildDirectionLinks(
        float $toLat,
        float $toLng,
        ?float $fromLat = null,
        ?float $fromLng = null,
        ?string $fromAddress = null,
    ): array {
        $dest = "{$toLat},{$toLng}";

        if ($fromLat !== null && $fromLng !== null) {
            $origin = "{$fromLat},{$fromLng}";

            return [
                'google_maps' => "https://www.google.com/maps/dir/?api=1&origin={$origin}&destination={$dest}&travelmode=driving",
                'apple_maps' => "https://maps.apple.com/?saddr={$origin}&daddr={$dest}&dirflg=d",
                'waze' => "https://waze.com/ul?ll={$dest}&from={$origin}&navigate=yes",
                'openstreetmap' => "https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route={$fromLat}%2C{$fromLng}%3B{$toLat}%2C{$toLng}",
            ];
        }

        if ($fromAddress !== null && trim($fromAddress) !== '') {
            $encoded = rawurlencode(trim($fromAddress));

            return [
                'google_maps' => "https://www.google.com/maps/dir/?api=1&origin={$encoded}&destination={$dest}&travelmode=driving",
                'apple_maps' => "https://maps.apple.com/?saddr={$encoded}&daddr={$dest}&dirflg=d",
                'waze' => "https://waze.com/ul?ll={$dest}&navigate=yes",
                'openstreetmap' => "https://www.openstreetmap.org/?mlat={$toLat}&mlon={$toLng}#map=16/{$toLat}/{$toLng}",
            ];
        }

        return [
            'google_maps' => "https://www.google.com/maps/dir/?api=1&destination={$dest}&travelmode=driving",
            'apple_maps' => "https://maps.apple.com/?daddr={$dest}&dirflg=d",
            'waze' => "https://waze.com/ul?ll={$dest}&navigate=yes",
            'openstreetmap' => "https://www.openstreetmap.org/?mlat={$toLat}&mlon={$toLng}#map=16/{$toLat}/{$toLng}",
        ];
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * (sin($dLon / 2) ** 2);

        $c = 2 * atan2(sqrt($a), sqrt(max(0.0, 1 - $a)));

        return $earthRadiusKm * $c;
    }

    private function compassBearing(float $lat1, float $lon1, float $lat2, float $lon2): string
    {
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dLambda = deg2rad($lon2 - $lon1);

        $y = sin($dLambda) * cos($phi2);
        $x = cos($phi1) * sin($phi2) - sin($phi1) * cos($phi2) * cos($dLambda);
        $deg = (rad2deg(atan2($y, $x)) + 360) % 360;

        $dirs = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];

        return $dirs[(int) round($deg / 45) % 8];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveWorkingHours(): array
    {
        return WorkingHour::query()
            ->select(['day_of_week', 'open_time', 'close_time', 'is_closed'])
            ->distinct()
            ->orderBy('day_of_week')
            ->get()
            ->map(fn (WorkingHour $wh): array => [
                'day_of_week' => $wh->day_of_week,
                'open_time' => substr((string) $wh->open_time, 0, 5),
                'close_time' => substr((string) $wh->close_time, 0, 5),
                'is_closed' => (bool) $wh->is_closed,
            ])
            ->values()
            ->all();
    }
}
