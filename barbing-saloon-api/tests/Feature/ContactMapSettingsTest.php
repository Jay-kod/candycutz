<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('persists a valid google maps iframe embed url through sanitization and rejects untrusted iframes', function (): void {
    $admin = User::factory()->admin()->create();
    $branch = Branch::factory()->create([
        'name' => 'CandyCutz Studio',
        'address' => 'Old Address',
        'latitude' => 8.8486,
        'longitude' => 7.8736,
        'is_active' => true,
    ]);

    $embedSrc = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3941.5!2d7.891234!3d8.862345!2m3!1f0!2f0!3f0';
    $iframeHtml = '<iframe src="'.$embedSrc.'" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>';

    $saveRes = $this->actingAs($admin)
        ->postJson('/api/v1/admin/settings', [
            'settings' => [
                'contact_address' => 'New Studio Plaza, Keffi',
                'contact_map_iframe' => $iframeHtml,
                'contact_map_link' => 'https://maps.app.goo.gl/example',
            ],
        ])
        ->assertOk();

    expect($saveRes->json('data.contact_map_iframe'))->toBe($embedSrc);
    expect(Setting::where('key', 'contact_map_iframe')->value('value'))->toBe($embedSrc);
    expect((float) Setting::where('key', 'latitude')->value('value'))->toBe(8.862345);
    expect((float) Setting::where('key', 'longitude')->value('value'))->toBe(7.891234);

    // Active branch and public shop/location endpoint reflect the updated coordinates and address immediately
    $branch->refresh();
    expect((float) $branch->latitude)->toBe(8.862345);
    expect((float) $branch->longitude)->toBe(7.891234);
    expect($branch->address)->toBe('New Studio Plaza, Keffi');

    $shopRes = $this->getJson('/api/v1/shop/location')->assertOk();
    expect($shopRes->json('data.latitude'))->toBe(8.862345);
    expect($shopRes->json('data.longitude'))->toBe(7.891234);
    expect($shopRes->json('data.address'))->toBe('New Studio Plaza, Keffi');
    expect($shopRes->json('data.map_iframe'))->toBe($embedSrc);
    expect($shopRes->json('data.location_lock.verified'))->toBeTrue();
    expect($shopRes->json('data.location_lock.status'))->toBe('locked');

    // When admin pastes a new map embed while still submitting old unmodified stored coords, backend auto-syncs to new pin coords
    $newEmbed = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3941.5!2d7.870000!3d8.840000!3m3!1m2!1s0x1!2sCandyCutz!3d8.851111!4d7.878888';
    $this->actingAs($admin)
        ->postJson('/api/v1/admin/settings', [
            'settings' => [
                'contact_map_iframe' => $newEmbed,
                'latitude' => '8.862345',
                'longitude' => '7.891234',
            ],
        ])
        ->assertOk();

    $syncedShop = $this->getJson('/api/v1/shop/location')->assertOk();
    expect($syncedShop->json('data.latitude'))->toBe(8.851111);
    expect($syncedShop->json('data.longitude'))->toBe(7.878888);
    expect($syncedShop->json('data.location_lock.status'))->toBe('locked');

    // If stored coords deliberately diverge from the embedded map pin by >150m, location_lock surfaces sync_mismatch
    Setting::updateOrCreate(['key' => 'latitude'], ['value' => '8.900000', 'group' => 'contact']);
    Cache::forget('catalogue.shop_location');
    $mismatchRes = $this->getJson('/api/v1/shop/location')->assertOk();
    expect($mismatchRes->json('data.location_lock.verified'))->toBeFalse();
    expect($mismatchRes->json('data.location_lock.status'))->toBe('sync_mismatch');

    // Untrusted iframe sources are still stripped to empty string
    $this->actingAs($admin)
        ->postJson('/api/v1/admin/settings', [
            'settings' => [
                'contact_map_iframe' => '<iframe src="https://evil.example.com/steal"></iframe>',
            ],
        ])
        ->assertOk();

    expect(Setting::where('key', 'contact_map_iframe')->value('value'))->toBe('');
});

it('prefers the exact place pin from a google maps short link over an iframe camera center offset', function (): void {
    Http::fake([
        'https://maps.app.goo.gl/*' => Http::response('', 302, [
            'Location' => 'https://www.google.com/maps/place/Candycutz/@8.8545413,7.9128165,861m/data=!3m1!1s0x1!8m2!3d8.8545413!4d7.9153914',
        ]),
    ]);

    $admin = User::factory()->admin()->create();
    $branch = Branch::factory()->create([
        'name' => 'Keffi Main Hub',
        'latitude' => 8.854541,
        'longitude' => 7.912816,
        'is_active' => true,
    ]);

    $viewportOnlyIframe = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3747.2!2d7.9128164!3d8.8545413!2m3!1f0!2f0!3f0!3m3!1m2!1s0x1!2sCandycutz';

    $this->actingAs($admin)
        ->postJson('/api/v1/admin/settings', [
            'settings' => [
                'contact_map_iframe' => $viewportOnlyIframe,
                'contact_map_link' => 'https://maps.app.goo.gl/candycutzPin',
                'latitude' => '8.854541',
                'longitude' => '7.912816',
            ],
        ])
        ->assertOk();

    $branch->refresh();
    expect((float) $branch->latitude)->toBe(8.854541);
    expect((float) $branch->longitude)->toBe(7.915391);

    $shopRes = $this->getJson('/api/v1/shop/location')->assertOk();
    expect($shopRes->json('data.name'))->toBe('Candycutz');
    expect($shopRes->json('data.latitude'))->toBe(8.854541);
    expect($shopRes->json('data.longitude'))->toBe(7.915391);
    expect($shopRes->json('data.location_lock.verified'))->toBeTrue();
});
