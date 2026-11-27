<?php

declare(strict_types=1);

namespace App\Domain\Catalogue\Services;

use App\Models\Branch;
use App\Models\Setting;
use App\Models\WorkingHour;
use App\Support\InputSanitizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
            'saloon_name',
            'brand_name',
            'site_name',
            'contact_shop_name',
            'address',
            'contact_address',
            'latitude',
            'longitude',
            'phone',
            'contact_phone',
            'whatsapp',
            'social_whatsapp',
            'contact_hours',
            'map_link',
            'contact_map_link',
            'map_iframe',
            'contact_map_iframe',
        ])->pluck('value', 'key');

        $rawMapIframe = $this->firstNonEmptySetting($settings, ['contact_map_iframe', 'map_iframe']);
        $mapIframe = InputSanitizer::cleanGoogleMapsEmbed($rawMapIframe);
        $rawMapLink = $this->firstNonEmptySetting($settings, ['contact_map_link', 'map_link']);
        $authoritativePin = InputSanitizer::resolveAuthoritativeMapPin($mapIframe, $rawMapLink);
        $iframeViewportLoc = InputSanitizer::extractGoogleMapsLocation($mapIframe);
        $embedPlaceName = InputSanitizer::extractGoogleMapsPlaceName($mapIframe, $rawMapLink);

        $settingName = $this->firstNonEmptySetting($settings, ['saloon_name', 'brand_name', 'site_name', 'contact_shop_name']);
        $name = $branchId !== null
            ? ($branch?->name ?: ($settingName ?: ($embedPlaceName ?: self::DEFAULT_NAME)))
            : ($settingName ?: ($embedPlaceName ?: ($branch?->name ?: self::DEFAULT_NAME)));
        $settingAddress = $this->firstNonEmptySetting($settings, ['contact_address', 'address']);
        $address = ($branchId === null && $settingAddress !== '')
            ? $settingAddress
            : ($branch?->address ?: ($settingAddress !== '' ? $settingAddress : self::DEFAULT_ADDRESS));

        $hasSettingCoords = $branchId === null
            && is_numeric($settings['latitude'] ?? null)
            && is_numeric($settings['longitude'] ?? null);

        $lat = $hasSettingCoords
            ? (float) $settings['latitude']
            : ($branch->latitude ?? (is_numeric($settings['latitude'] ?? null) ? (float) $settings['latitude'] : self::DEFAULT_LATITUDE));
        $lng = $hasSettingCoords
            ? (float) $settings['longitude']
            : ($branch->longitude ?? (is_numeric($settings['longitude'] ?? null) ? (float) $settings['longitude'] : self::DEFAULT_LONGITUDE));

        // Promote to the exact place pin when DB holds default coords OR viewport-shifted iframe camera center
        $usingDefaultCoords = abs((float) $lat - self::DEFAULT_LATITUDE) < 0.0001 && abs((float) $lng - self::DEFAULT_LONGITUDE) < 0.0001;
        $storedMatchesViewportOnly = $iframeViewportLoc !== null
            && ! $iframeViewportLoc['precise']
            && abs((float) $lat - $iframeViewportLoc['lat']) < 0.00001
            && abs((float) $lng - $iframeViewportLoc['lng']) < 0.00001;
        if ($branchId === null && $authoritativePin !== null && (! $hasSettingCoords || $usingDefaultCoords || ($storedMatchesViewportOnly && $authoritativePin['precise']))) {
            $lat = $authoritativePin['lat'];
            $lng = $authoritativePin['lng'];
        }

        $settingPhone = $this->firstNonEmptySetting($settings, ['contact_phone', 'phone']);
        $phone = ($branchId === null && $settingPhone !== '')
            ? $settingPhone
            : ($branch?->phone ?: ($settingPhone !== '' ? $settingPhone : '+234 810 000 0000'));
        $whatsapp = $this->firstNonEmptySetting($settings, ['social_whatsapp', 'whatsapp']) ?: $phone;
        $mapLink = $rawMapLink !== '' ? $rawMapLink : "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";

        $roundedLat = round((float) $lat, 6);
        $roundedLng = round((float) $lng, 6);

        // Replace stale seeder placeholder ("123 Grooming Street / Lagos") when shop pin is in another city
        $isPlaceholderAddress = stripos($address, '123 grooming street') !== false;
        if ($isPlaceholderAddress && $this->haversineKm($roundedLat, $roundedLng, 6.5244, 3.3792) > 80.0) {
            $address = self::DEFAULT_ADDRESS;
        }

        return [
            'branch_id' => $branch?->id,
            'name' => $name,
            'address' => $address,
            'latitude' => $roundedLat,
            'longitude' => $roundedLng,
            'locked' => true,
            'phone' => $phone,
            'whatsapp' => $whatsapp,
            'hours_summary' => $this->firstNonEmptySetting($settings, ['contact_hours']) ?: 'Mon–Sat 8:00 AM – 8:00 PM',
            'working_hours' => $this->resolveWorkingHours(),
            'map_link' => $mapLink,
            'map_iframe' => $mapIframe !== '' ? $mapIframe : null,
            'location_lock' => $this->buildLocationLockReport($roundedLat, $roundedLng, $mapIframe, $rawMapLink, $branchId !== null ? $branch : null),
            'directions_links' => $this->buildDirectionLinks($roundedLat, $roundedLng),
        ];
    }

    /**
     * @param  Collection<string, mixed>  $settings
     * @param  array<int, string>  $keys
     */
    private function firstNonEmptySetting(Collection $settings, array $keys): string
    {
        foreach ($keys as $key) {
            $val = trim((string) ($settings[$key] ?? ''));
            if ($val !== '') {
                return $val;
            }
        }

        return '';
    }

    /**
     * Report whether the locked page coordinates, active branch, and Google Map embed agree.
     *
     * @return array<string, mixed>
     */
    private function buildLocationLockReport(
        float $lat,
        float $lng,
        string $mapIframe,
        string $mapLink,
        ?Branch $branch,
    ): array {
        $hasValidCoords = $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180 && ($lat !== 0.0 || $lng !== 0.0);
        $pin = InputSanitizer::resolveAuthoritativeMapPin($mapIframe, $mapLink);
        $embedCoords = $pin !== null ? [$pin['lat'], $pin['lng']] : null;
        $maxMapDriftMeters = ($pin !== null && $pin['precise']) ? 150 : 500;
        $driftMeters = $hasValidCoords && $embedCoords !== null
            ? (int) round($this->haversineKm($lat, $lng, $embedCoords[0], $embedCoords[1]) * 1000)
            : null;

        $branchDriftMeters = ($branch && is_numeric($branch->latitude) && is_numeric($branch->longitude))
            ? (int) round($this->haversineKm($lat, $lng, (float) $branch->latitude, (float) $branch->longitude) * 1000)
            : null;

        if (! $hasValidCoords) {
            return [
                'verified' => false,
                'status' => 'coords_missing',
                'message' => 'Main shop coordinates are not configured. Set shop latitude/longitude or embed a Google Map in Admin Contact settings.',
                'embed_latitude' => $embedCoords[0] ?? null,
                'embed_longitude' => $embedCoords[1] ?? null,
                'drift_meters' => $driftMeters,
            ];
        }

        if ($mapIframe === '') {
            return [
                'verified' => false,
                'status' => 'embed_missing',
                'message' => 'Google Maps embed is missing in Admin Contact settings. Customer Directions is using saved shop coordinates.',
                'embed_latitude' => $embedCoords[0] ?? null,
                'embed_longitude' => $embedCoords[1] ?? null,
                'drift_meters' => $driftMeters,
            ];
        }

        if (($driftMeters !== null && $driftMeters > $maxMapDriftMeters) || ($branchDriftMeters !== null && $branchDriftMeters > 150)) {
            $worstDrift = max($driftMeters ?? 0, $branchDriftMeters ?? 0);

            return [
                'verified' => false,
                'status' => 'sync_mismatch',
                'message' => "Shop page coordinates and Google Map pin differ by {$worstDrift} m. Re-save Admin Contact settings to lock them together.",
                'embed_latitude' => $embedCoords[0] ?? null,
                'embed_longitude' => $embedCoords[1] ?? null,
                'drift_meters' => $worstDrift,
            ];
        }

        return [
            'verified' => true,
            'status' => 'locked',
            'message' => "Main shop location is locked to the page and map ({$lat}, {$lng}).",
            'embed_latitude' => $embedCoords[0] ?? $lat,
            'embed_longitude' => $embedCoords[1] ?? $lng,
            'drift_meters' => $driftMeters ?? 0,
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

    /**
     * Geocode a street/landmark/coordinates/Google Maps URL biased tightly around the locked shop location.
     *
     * @return array<int, array<string, mixed>>
     */
    public function geocodeSearch(string $query): array
    {
        $trimmed = trim($query);
        if ($trimmed === '') {
            return [];
        }

        // 1. Direct lat,lng coordinate input
        if (preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*[, ]\s*(-?\d+(?:\.\d+)?)\s*$/', $trimmed, $m) === 1) {
            $lat = round((float) $m[1], 6);
            $lng = round((float) $m[2], 6);
            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                $resolved = $this->reverseGeocode($lat, $lng);

                return [[
                    'label' => $resolved['label'],
                    'street' => $resolved['street'],
                    'city' => $resolved['city'],
                    'state' => $resolved['state'],
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'source' => 'coordinates',
                ]];
            }
        }

        // 2. Pasted Google Maps URL or short share link
        if (stripos($trimmed, 'http') === 0 || stripos($trimmed, 'maps.') !== false || stripos($trimmed, 'goo.gl') !== false) {
            $resolvedUrl = InputSanitizer::resolveGoogleMapsShareUrl($trimmed);
            $loc = InputSanitizer::extractGoogleMapsLocation($resolvedUrl);
            if ($loc !== null) {
                $resolved = $this->reverseGeocode($loc['lat'], $loc['lng']);

                return [[
                    'label' => $resolved['label'],
                    'street' => $resolved['street'],
                    'city' => $resolved['city'],
                    'state' => $resolved['state'],
                    'latitude' => $loc['lat'],
                    'longitude' => $loc['lng'],
                    'source' => 'google_link',
                ]];
            }
        }

        $shop = $this->getShopLocation();
        $shopLat = (float) ($shop['latitude'] ?? self::DEFAULT_LATITUDE);
        $shopLng = (float) ($shop['longitude'] ?? self::DEFAULT_LONGITUDE);
        $cacheKey = 'directions.geo.'.sha1(strtolower($trimmed)."|{$shopLat},{$shopLng}");

        return Cache::remember($cacheKey, 300, function () use ($trimmed, $shopLat, $shopLng): array {
            $results = array_merge(
                $this->queryPhotonGeocode($trimmed, $shopLat, $shopLng),
                $this->queryNominatimGeocode($trimmed, $shopLat, $shopLng)
            );

            $deduped = [];
            foreach ($results as $item) {
                $lat = (float) $item['latitude'];
                $lng = (float) $item['longitude'];
                $distKm = $this->haversineKm($shopLat, $shopLng, $lat, $lng);

                // Reject wild cross-country/cross-continent noise (> 120 km away from shop)
                if ($distKm > 120.0) {
                    continue;
                }

                $key = sprintf('%.4f,%.4f', $lat, $lng);
                if (! isset($deduped[$key])) {
                    $item['distance_km'] = round($distKm, 2);
                    $deduped[$key] = $item;
                }
            }

            $list = array_values($deduped);
            usort($list, fn (array $a, array $b): int => ($a['distance_km'] ?? 999) <=> ($b['distance_km'] ?? 999));

            return array_slice($list, 0, 5);
        });
    }

    /**
     * Reverse-geocode coordinates server-side with clean Nigerian locality formatting and Keffi/Kokona cleanup.
     *
     * @return array{label: string, street: string, city: string, state: string}
     */
    public function reverseGeocode(float $lat, float $lng): array
    {
        $roundedLat = round($lat, 6);
        $roundedLng = round($lng, 6);
        $fallbackStreet = sprintf('Pinned Location (%.5f, %.5f)', $roundedLat, $roundedLng);
        $fallback = [
            'label' => $fallbackStreet,
            'street' => $fallbackStreet,
            'city' => 'Keffi',
            'state' => 'Nasarawa',
        ];

        $cacheKey = sprintf('directions.rev.%.5f,%.5f', $roundedLat, $roundedLng);

        return Cache::remember($cacheKey, 1800, function () use ($roundedLat, $roundedLng, $fallback, $fallbackStreet): array {
            $shop = $this->getShopLocation();
            $shopLat = (float) ($shop['latitude'] ?? self::DEFAULT_LATITUDE);
            $shopLng = (float) ($shop['longitude'] ?? self::DEFAULT_LONGITUDE);

            try {
                $resp = Http::timeout(4)
                    ->withHeaders(['User-Agent' => 'CandyCutzDirections/1.0', 'Accept' => 'application/json'])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'jsonv2',
                        'zoom' => 18,
                        'addressdetails' => 1,
                        'lat' => $roundedLat,
                        'lon' => $roundedLng,
                    ]);

                if ($resp->successful()) {
                    $data = $resp->json();
                    if (is_array($data)) {
                        $addr = is_array($data['address'] ?? null) ? $data['address'] : [];
                        $road = trim(implode(' ', array_filter([
                            $addr['house_number'] ?? null,
                            $addr['road'] ?? $addr['neighbourhood'] ?? $addr['suburb'] ?? $addr['quarter'] ?? $addr['hamlet'] ?? null,
                        ])));
                        $rawCity = (string) ($addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['county'] ?? 'Keffi');
                        $city = $this->normalizeLocalityCity($rawCity, $roundedLat, $roundedLng, $shopLat, $shopLng);
                        $state = (string) ($addr['state'] ?? 'Nasarawa');
                        $street = $road !== '' ? $road : $fallbackStreet;
                        $parts = array_values(array_unique(array_filter([$road !== '' ? $road : null, $city, $state])));

                        return [
                            'label' => $parts !== [] ? implode(', ', $parts) : $fallbackStreet,
                            'street' => $street,
                            'city' => $city,
                            'state' => $state,
                        ];
                    }
                }
            } catch (\Throwable) {
                // Fall back to Photon reverse or clean coordinate label
            }

            try {
                $photon = Http::timeout(4)
                    ->withHeaders(['User-Agent' => 'CandyCutzDirections/1.0', 'Accept' => 'application/json'])
                    ->get('https://photon.komoot.io/reverse', ['lat' => $roundedLat, 'lon' => $roundedLng]);

                $feat = $photon->successful() ? ($photon->json('features.0.properties') ?? null) : null;
                if (is_array($feat)) {
                    $street = trim((string) ($feat['street'] ?? $feat['name'] ?? $feat['district'] ?? '')) ?: $fallbackStreet;
                    $rawCity = (string) ($feat['city'] ?? $feat['county'] ?? 'Keffi');
                    $city = $this->normalizeLocalityCity($rawCity, $roundedLat, $roundedLng, $shopLat, $shopLng);
                    $state = (string) ($feat['state'] ?? 'Nasarawa');
                    $parts = array_values(array_unique(array_filter([$street !== $fallbackStreet ? $street : null, $city, $state])));

                    return [
                        'label' => $parts !== [] ? implode(', ', $parts) : $fallbackStreet,
                        'street' => $street,
                        'city' => $city,
                        'state' => $state,
                    ];
                }
            } catch (\Throwable) {
                // Keep clean coordinate fallback
            }

            return $fallback;
        });
    }

    private function normalizeLocalityCity(string $city, float $lat, float $lng, float $shopLat, float $shopLng): string
    {
        $trimmed = trim($city);
        if ($trimmed === '') {
            return 'Keffi';
        }
        if (strcasecmp($trimmed, 'Kokona') === 0 && $this->haversineKm($lat, $lng, $shopLat, $shopLng) <= 25.0) {
            return 'Keffi';
        }

        return $trimmed;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function queryPhotonGeocode(string $query, float $shopLat, float $shopLng): array
    {
        try {
            $resp = Http::timeout(4)
                ->withHeaders(['User-Agent' => 'CandyCutzDirections/1.0', 'Accept' => 'application/json'])
                ->get('https://photon.komoot.io/api', [
                    'q' => $query,
                    'lat' => $shopLat,
                    'lon' => $shopLng,
                    'limit' => 6,
                    'lang' => 'en',
                ]);

            if (! $resp->successful()) {
                return [];
            }

            $features = $resp->json('features');
            if (! is_array($features)) {
                return [];
            }

            $rows = [];
            foreach ($features as $feature) {
                $coords = $feature['geometry']['coordinates'] ?? null;
                $props = $feature['properties'] ?? null;
                if (! is_array($coords) || count($coords) < 2 || ! is_array($props)) {
                    continue;
                }

                $countryCode = strtoupper((string) ($props['countrycode'] ?? ''));
                if ($countryCode !== '' && $countryCode !== 'NG') {
                    continue;
                }

                $lat = round((float) $coords[1], 6);
                $lng = round((float) $coords[0], 6);
                $name = trim((string) ($props['name'] ?? $props['street'] ?? ''));
                $city = $this->normalizeLocalityCity((string) ($props['city'] ?? $props['town'] ?? 'Keffi'), $lat, $lng, $shopLat, $shopLng);
                $state = trim((string) ($props['state'] ?? 'Nasarawa'));
                $parts = array_values(array_unique(array_filter([$name, $props['district'] ?? null, $city, $state])));

                $rows[] = [
                    'label' => $parts !== [] ? implode(', ', $parts) : sprintf('Location (%.5f, %.5f)', $lat, $lng),
                    'street' => $name ?: sprintf('Pinned Location (%.5f, %.5f)', $lat, $lng),
                    'city' => $city ?: 'Keffi',
                    'state' => $state ?: 'Nasarawa',
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'source' => 'photon',
                ];
            }

            return $rows;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function queryNominatimGeocode(string $query, float $shopLat, float $shopLng): array
    {
        try {
            $viewbox = sprintf(
                '%.4f,%.4f,%.4f,%.4f',
                $shopLng - 0.35,
                $shopLat + 0.35,
                $shopLng + 0.35,
                $shopLat - 0.35
            );

            $resp = Http::timeout(4)
                ->withHeaders(['User-Agent' => 'CandyCutzDirections/1.0', 'Accept' => 'application/json'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'countrycodes' => 'ng',
                    'viewbox' => $viewbox,
                    'bounded' => 0,
                    'limit' => 5,
                    'q' => $query,
                ]);

            if (! $resp->successful()) {
                return [];
            }

            $rows = $resp->json();
            if (! is_array($rows)) {
                return [];
            }

            $out = [];
            foreach ($rows as $row) {
                if (! is_array($row) || ! isset($row['lat'], $row['lon'])) {
                    continue;
                }
                $lat = round((float) $row['lat'], 6);
                $lng = round((float) $row['lon'], 6);
                $addr = is_array($row['address'] ?? null) ? $row['address'] : [];
                $street = trim((string) ($addr['road'] ?? $addr['neighbourhood'] ?? $addr['suburb'] ?? $row['name'] ?? ''));
                $city = $this->normalizeLocalityCity((string) ($addr['city'] ?? $addr['town'] ?? $addr['village'] ?? 'Keffi'), $lat, $lng, $shopLat, $shopLng);
                $state = trim((string) ($addr['state'] ?? 'Nasarawa'));
                $parts = array_values(array_unique(array_filter([$street, $city, $state])));

                $out[] = [
                    'label' => $parts !== [] ? implode(', ', $parts) : (string) ($row['display_name'] ?? sprintf('Location (%.5f, %.5f)', $lat, $lng)),
                    'street' => $street ?: sprintf('Pinned Location (%.5f, %.5f)', $lat, $lng),
                    'city' => $city ?: 'Keffi',
                    'state' => $state ?: 'Nasarawa',
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'source' => 'nominatim',
                ];
            }

            return $out;
        } catch (\Throwable) {
            return [];
        }
    }
}
