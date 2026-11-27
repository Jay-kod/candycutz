<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Branch;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('returns locked shop location and calculates live directions from origin coordinates', function (): void {
    $branch = Branch::factory()->create([
        'name' => 'CandyCutz Keffi Flagship',
        'address' => 'Angwan Kare BCG, Keffi, Nasarawa',
        'latitude' => 8.8486,
        'longitude' => 7.8736,
        'phone' => '+2348011112222',
        'is_active' => true,
    ]);

    Setting::updateOrCreate(['key' => 'contact_hours'], ['value' => 'Mon-Sat 8am - 8pm', 'group' => 'contact']);

    $locRes = $this->getJson('/api/v1/shop/location')->assertOk();
    expect($locRes->json('data.name'))->toBe('CandyCutz Keffi Flagship');
    expect($locRes->json('data.locked'))->toBeTrue();
    expect($locRes->json('data.latitude'))->toBe(8.8486);
    expect($locRes->json('data.longitude'))->toBe(7.8736);
    expect($locRes->json('data.directions_links.google_maps'))->toContain('8.8486,7.8736');

    $branchRes = $this->getJson("/api/v1/branches/{$branch->id}/location")->assertOk();
    expect($branchRes->json('data.branch_id'))->toBe($branch->id);

    // About ~1.6 km away from shop
    $dirRes = $this->getJson('/api/v1/shop/directions?from_lat=8.8600&from_lng=7.8800')->assertOk();
    expect($dirRes->json('data.to.locked'))->toBeTrue();
    expect($dirRes->json('data.estimated_distance_km'))->toBeGreaterThan(1.0)->toBeLessThan(3.0);
    expect($dirRes->json('data.estimated_drive_minutes'))->toBeGreaterThanOrEqual(2);
    expect($dirRes->json('data.compass_bearing'))->toBe('SW');
    expect($dirRes->json('data.directions_links.google_maps'))->toContain('origin=8.86,7.88');
    expect($dirRes->json('data.directions_links.apple_maps'))->toContain('saddr=8.86,7.88');
    expect($dirRes->json('data.directions_links.waze'))->toContain('navigate=yes');
});

it('validates coordinate bounds on directions queries', function (): void {
    $this->getJson('/api/v1/shop/directions?from_lat=120&from_lng=7.8')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['from_lat']);
});

it('allows customers to manage saved addresses and prevents cross-user IDOR access', function (): void {
    $customer = User::factory()->customer()->create();
    $otherCustomer = User::factory()->customer()->create();

    $this->getJson('/api/v1/customer/addresses')->assertUnauthorized();

    $createFirst = $this->actingAs($customer)
        ->postJson('/api/v1/customer/addresses', [
            'title' => 'Home',
            'street' => '45 Unity Road',
            'landmark' => 'Behind Central Mosque',
            'city' => 'Keffi',
            'state' => 'Nasarawa',
            'latitude' => 8.855,
            'longitude' => 7.881,
        ])
        ->assertStatus(201);

    $firstId = $createFirst->json('data.id');
    expect($createFirst->json('data.is_default'))->toBeTrue();

    $createSecond = $this->actingAs($customer)
        ->postJson('/api/v1/customer/addresses', [
            'title' => 'Office',
            'street' => '12 Hospital Road',
            'city' => 'Keffi',
            'state' => 'Nasarawa',
            'is_default' => true,
        ])
        ->assertStatus(201);

    $secondId = $createSecond->json('data.id');
    expect(Address::findOrFail($firstId)->is_default)->toBeFalse();
    expect(Address::findOrFail($secondId)->is_default)->toBeTrue();

    $listRes = $this->actingAs($customer)->getJson('/api/v1/customer/addresses')->assertOk();
    expect($listRes->json('data'))->toHaveCount(2);

    // Other customer cannot modify or delete owner's address
    $this->actingAs($otherCustomer)->patchJson("/api/v1/customer/addresses/{$firstId}/default")->assertForbidden();
    $this->actingAs($otherCustomer)->deleteJson("/api/v1/customer/addresses/{$firstId}")->assertForbidden();

    // Owner restores first address as default and deletes second
    $this->actingAs($customer)->patchJson("/api/v1/customer/addresses/{$firstId}/default")->assertOk();
    expect(Address::findOrFail($firstId)->is_default)->toBeTrue();

    $this->actingAs($customer)->deleteJson("/api/v1/customer/addresses/{$secondId}")->assertOk();
    expect(Address::whereKey($secondId)->exists())->toBeFalse();

    // Owner can save edits to an existing address (label + updated pin coordinates)
    $updateRes = $this->actingAs($customer)
        ->putJson("/api/v1/customer/addresses/{$firstId}", [
            'title' => 'Home Updated',
            'street' => '45 Unity Road (Gate 2)',
            'city' => 'Keffi',
            'state' => 'Nasarawa',
            'latitude' => 8.857777,
            'longitude' => 7.914444,
        ])
        ->assertOk();

    expect($updateRes->json('data.title'))->toBe('Home Updated');
    expect($updateRes->json('data.latitude'))->toBe(8.857777);
    expect($updateRes->json('data.longitude'))->toBe(7.914444);
});

it('allows saving a pinned coordinate address without an explicit street and rejects unpaired coordinates', function (): void {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)
        ->postJson('/api/v1/customer/addresses', [
            'title' => 'Pinned Spot',
            'latitude' => 8.85123,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['longitude']);

    $pinnedRes = $this->actingAs($customer)
        ->postJson('/api/v1/customer/addresses', [
            'title' => 'Pinned Spot',
            'latitude' => 8.85123,
            'longitude' => 7.87654,
        ])
        ->assertStatus(201);

    expect($pinnedRes->json('data.street'))->toBe('Pinned Location (8.85123, 7.87654)');
    expect($pinnedRes->json('data.latitude'))->toBe(8.85123);
    expect($pinnedRes->json('data.longitude'))->toBe(7.87654);
});

it('prioritizes admin contact page settings and map embed pin over stale defaults on shop location', function (): void {
    Branch::factory()->create([
        'name' => 'Keffi Main Hub',
        'address' => 'Old Branch Address',
        'latitude' => 8.8486,
        'longitude' => 7.8736,
        'phone' => '+2348000000000',
        'is_active' => true,
    ]);

    Setting::updateOrCreate(['key' => 'contact_address'], ['value' => '', 'group' => 'contact']);
    Setting::updateOrCreate(['key' => 'address'], ['value' => 'Angwan Kare Plaza, Keffi', 'group' => 'contact']);
    Setting::updateOrCreate(['key' => 'contact_phone'], ['value' => '+2348162661713', 'group' => 'contact']);
    Setting::updateOrCreate(['key' => 'latitude'], ['value' => '8.84860000', 'group' => 'contact']);
    Setting::updateOrCreate(['key' => 'longitude'], ['value' => '7.87360000', 'group' => 'contact']);
    Setting::updateOrCreate([
        'key' => 'contact_map_iframe',
    ], [
        'value' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3747.2!2d7.912816!3d8.854541!3m3!1m2!1s0x1!2sCandycutz',
        'group' => 'contact',
    ]);

    $res = $this->getJson('/api/v1/shop/location')->assertOk();
    expect($res->json('data.address'))->toBe('Angwan Kare Plaza, Keffi');
    expect($res->json('data.phone'))->toBe('+2348162661713');
    expect($res->json('data.latitude'))->toBe(8.854541);
    expect($res->json('data.longitude'))->toBe(7.912816);
    expect($res->json('data.location_lock.verified'))->toBeTrue();
    expect($res->headers->get('Permissions-Policy'))->toContain('geolocation=(self)');
});

it('geocodes keffi landmarks with regional bounds, supports coordinate/map links, and strips kokona noise on reverse geocode', function (): void {
    Setting::updateOrCreate(['key' => 'latitude'], ['value' => '8.854541', 'group' => 'contact']);
    Setting::updateOrCreate(['key' => 'longitude'], ['value' => '7.915391', 'group' => 'contact']);
    Setting::updateOrCreate(['key' => 'contact_address'], ['value' => "123 Grooming Street\nLagos, Nigeria", 'group' => 'contact']);

    // Placeholder Lagos address is automatically replaced when the shop pin is in Keffi
    $shopRes = $this->getJson('/api/v1/shop/location')->assertOk();
    expect($shopRes->json('data.address'))->not->toContain('Lagos');

    Http::fake([
        'https://photon.komoot.io/api*' => Http::response([
            'features' => [
                [
                    'geometry' => ['coordinates' => [3.3792, 6.5244]], // Lagos >450 km away (should be filtered out)
                    'properties' => ['countrycode' => 'NG', 'name' => 'NSUK Distraction', 'city' => 'Lagos', 'state' => 'Lagos'],
                ],
                [
                    'geometry' => ['coordinates' => [7.9012, 8.8471]], // ~1.7 km in Keffi
                    'properties' => ['countrycode' => 'NG', 'name' => 'NSUK Main Gate', 'city' => 'Kokona', 'state' => 'Nasarawa'],
                ],
            ],
        ], 200),
        'https://nominatim.openstreetmap.org/search*' => Http::response([], 200),
        'https://nominatim.openstreetmap.org/reverse*' => Http::response([
            'address' => [
                'road' => 'Angwan Kare Road',
                'county' => 'Kokona',
                'state' => 'Nasarawa',
            ],
        ], 200),
    ]);

    $geoRes = $this->getJson('/api/v1/shop/geocode?q=NSUK+Main+Gate')->assertOk();
    $rows = $geoRes->json('data');
    expect($rows)->toHaveCount(1);
    expect($rows[0]['city'])->toBe('Keffi');
    expect($rows[0]['label'])->toContain('NSUK Main Gate');

    $revRes = $this->getJson('/api/v1/shop/reverse-geocode?lat=8.854541&lng=7.915391')->assertOk();
    expect($revRes->json('data.city'))->toBe('Keffi');
    expect($revRes->json('data.street'))->toBe('Angwan Kare Road');
    expect($revRes->json('data.label'))->not->toContain('Kokona');

    // Direct coordinate parsing works immediately without external call ambiguity
    $coordRes = $this->getJson('/api/v1/shop/geocode?q=8.854541,7.915391')->assertOk();
    expect($coordRes->json('data.0.latitude'))->toBe(8.854541);
    expect($coordRes->json('data.0.longitude'))->toBe(7.915391);
    expect($coordRes->json('data.0.source'))->toBe('coordinates');
});
