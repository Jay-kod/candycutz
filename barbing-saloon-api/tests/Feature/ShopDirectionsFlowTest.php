<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Branch;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
});
