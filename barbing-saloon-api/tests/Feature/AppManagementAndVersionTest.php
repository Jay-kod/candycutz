<?php

use App\Domain\Shared\Enums\UserRole;
use App\Models\AppCrash;
use App\Models\AppVersion;
use App\Models\FeatureFlag;
use App\Models\Setting;
use App\Models\User;

it('returns 426 upgrade required when mobile app version is lower than minimum version', function () {
    AppVersion::create([
        'version' => '2.0.0',
        'platform' => 'android',
        'is_minimum' => true,
        'is_latest' => true,
        'release_notes' => 'Critical update required.',
    ]);

    $response = $this->withHeaders([
        'X-Client-Type' => 'mobile',
        'X-App-Version' => '1.0.0',
        'X-App-Platform' => 'android',
    ])->getJson('/api/v1/settings');

    $response->assertStatus(426);
    $response->assertJsonPath('code', 'UPGRADE_REQUIRED');
    $response->assertJsonPath('data.force_update', true);
    $response->assertJsonPath('data.min_version', '2.0.0');
});

it('allows mobile requests when version meets or exceeds minimum requirement', function () {
    AppVersion::create([
        'version' => '1.0.0',
        'platform' => 'android',
        'is_minimum' => true,
        'is_latest' => true,
    ]);

    $response = $this->withHeaders([
        'X-Client-Type' => 'mobile',
        'X-App-Version' => '1.0.0',
        'X-App-Platform' => 'android',
    ])->getJson('/api/v1/settings');

    $response->assertStatus(200);
});

it('returns 503 maintenance mode when app maintenance is turned on', function () {
    Setting::updateOrCreate(
        ['key' => 'app_maintenance_mode'],
        ['value' => '1', 'type' => 'boolean', 'group' => 'app']
    );
    Setting::updateOrCreate(
        ['key' => 'app_maintenance_message'],
        ['value' => 'CandyCutz is undergoing scheduled maintenance.', 'type' => 'text', 'group' => 'app']
    );

    $response = $this->withHeaders([
        'X-Client-Type' => 'mobile',
    ])->getJson('/api/v1/services');

    $response->assertStatus(503);
    $response->assertJsonPath('code', 'MAINTENANCE_MODE');
    $response->assertJsonPath('maintenance', true);

    // Reset setting so subsequent tests aren't affected
    Setting::where('key', 'app_maintenance_mode')->update(['value' => '0']);
});

it('records mobile app crash telemetry via POST /api/v1/app/crashes', function () {
    $response = $this->postJson('/api/v1/app/crashes', [
        'error_message' => 'Uncaught TypeError: Cannot read properties of undefined',
        'stack_trace' => "TypeError: Cannot read properties of undefined\n    at Component (app/index.tsx:42:15)",
        'app_version' => '1.0.0',
        'platform' => 'android',
        'device_info' => ['os' => 'Android 14', 'model' => 'Pixel 7'],
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('success', true);

    expect(AppCrash::count())->toBeGreaterThan(0);
    $crash = AppCrash::latest('id')->first();
    expect($crash->error_message)->toContain('Uncaught TypeError');
    expect($crash->platform)->toBe('android');
    expect($crash->app_version)->toBe('1.0.0');
});

it('returns active feature flags via public API', function () {
    FeatureFlag::updateOrCreate(
        ['key' => 'barber_live_queue_v2'],
        [
            'name' => 'Live Queue V2',
            'is_active' => true,
            'rollout_percentage' => 100,
            'enabled_for' => ['web', 'app'],
        ]
    );

    $response = $this->getJson('/api/v1/feature-flags?platform=app');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.flags.barber_live_queue_v2', true);
});

it('formats appointment source and source_label in AppointmentResource', function () {
    $appointment = new \App\Models\Appointment([
        'id' => 101,
        'appointment_date' => '2026-09-25',
        'appointment_time' => '10:00',
        'source' => \App\Domain\Shared\Enums\AppointmentSource::app,
        'status' => \App\Domain\Shared\Enums\AppointmentStatus::confirmed,
    ]);

    $resource = (new \App\Http\Resources\AppointmentResource($appointment))->toArray(request());

    expect($resource['source'])->toBe('app');
    expect($resource['source_label'])->toBe('Mobile App');
});
