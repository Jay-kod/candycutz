<?php

use App\Domain\Content\Services\AnimationSettingsService;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('loads default animation settings from public /api/v1/animations endpoint', function () {
    $response = $this->getJson('/api/v1/animations');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.enabled', true);
    $response->assertJsonPath('data.default_type', 'fade-up');
    $response->assertJsonPath('data.respect_reduced_motion', true);
    $response->assertJsonStructure([
        'data' => [
            'enabled',
            'default_type',
            'duration_ms',
            'delay_ms',
            'easing',
            'distance',
            'stagger_ms',
            'trigger_threshold',
            'root_margin',
            'once',
            'disable_on_mobile',
            'respect_reduced_motion',
            'overrides',
        ],
    ]);
});

it('includes resolved animation config in public /api/v1/settings response', function () {
    $response = $this->getJson('/api/v1/settings');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.animation_resolved.enabled', true);
    $response->assertJsonPath('data.animation_resolved.default_type', 'fade-up');
});

it('forbids unauthenticated and non-admin users from admin animations endpoints', function () {
    // Unauthenticated
    $this->getJson('/api/v1/admin/animations')->assertStatus(401);
    $this->postJson('/api/v1/admin/animations', [])->assertStatus(401);
    $this->postJson('/api/v1/admin/animations/reset')->assertStatus(401);

    // Customer
    $customer = User::factory()->customer()->create();
    $this->actingAs($customer)->getJson('/api/v1/admin/animations')->assertStatus(403);
    $this->actingAs($customer)->postJson('/api/v1/admin/animations', [])->assertStatus(403);
    $this->actingAs($customer)->postJson('/api/v1/admin/animations/reset')->assertStatus(403);

    // Barber
    $barber = User::factory()->barber()->create();
    $this->actingAs($barber)->getJson('/api/v1/admin/animations')->assertStatus(403);
    $this->actingAs($barber)->postJson('/api/v1/admin/animations', [])->assertStatus(403);
    $this->actingAs($barber)->postJson('/api/v1/admin/animations/reset')->assertStatus(403);
});

it('allows admin users to view full animation configuration with metadata', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/animations');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonStructure([
        'data' => [
            'settings',
            'allowed_types',
            'allowed_override_types',
            'allowed_easings',
            'defaults',
        ],
    ]);

    $allowed = $response->json('data.allowed_types');
    expect($allowed)->toContain('fade-up', 'zoom-in', 'slide-left', 'none', 'blur-in');
});

it('rejects invalid animation settings updates with 422 validation errors', function () {
    $admin = User::factory()->admin()->create();

    // Invalid type
    $this->actingAs($admin)->postJson('/api/v1/admin/animations', [
        'enabled' => true,
        'default_type' => 'crazy-spin', // Invalid type
        'duration_ms' => 700,
        'delay_ms' => 0,
        'easing' => 'ease',
        'distance' => '30px',
        'stagger_ms' => 100,
        'trigger_threshold' => 0.2,
        'root_margin' => '0px',
        'once' => true,
        'disable_on_mobile' => false,
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['default_type']);

    // Out of range duration (too high)
    $this->actingAs($admin)->postJson('/api/v1/admin/animations', [
        'enabled' => true,
        'default_type' => 'fade-up',
        'duration_ms' => 10000, // Max is 3000
        'delay_ms' => 0,
        'easing' => 'ease',
        'distance' => '30px',
        'stagger_ms' => 100,
        'trigger_threshold' => 0.2,
        'root_margin' => '0px',
        'once' => true,
        'disable_on_mobile' => false,
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['duration_ms']);

    // Invalid distance unit
    $this->actingAs($admin)->postJson('/api/v1/admin/animations', [
        'enabled' => true,
        'default_type' => 'fade-up',
        'duration_ms' => 700,
        'delay_ms' => 0,
        'easing' => 'ease',
        'distance' => '30vw', // Must be px or rem
        'stagger_ms' => 100,
        'trigger_threshold' => 0.2,
        'root_margin' => '0px',
        'once' => true,
        'disable_on_mobile' => false,
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['distance']);
});

it('updates animation settings, invalidates cache, and persists to database', function () {
    $admin = User::factory()->admin()->create();

    $payload = [
        'enabled' => true,
        'default_type' => 'zoom-in',
        'duration_ms' => 850,
        'delay_ms' => 50,
        'easing' => 'ease-out',
        'distance' => '40px',
        'stagger_ms' => 150,
        'trigger_threshold' => 0.25,
        'root_margin' => '0px 0px -50px 0px',
        'once' => false,
        'disable_on_mobile' => true,
        'overrides' => [
            'home' => [
                'hero' => ['type' => 'fade', 'duration_ms' => 400],
                'features' => ['type' => 'slide-up', 'stagger_ms' => 200],
            ],
        ],
    ];

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/animations', $payload);

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.default_type', 'zoom-in');
    $response->assertJsonPath('data.duration_ms', 850);
    $response->assertJsonPath('data.once', false);
    $response->assertJsonPath('data.disable_on_mobile', true);
    $response->assertJsonPath('data.overrides.home.features.type', 'slide-up');

    // Confirm database persistence
    expect(Setting::where('key', 'animation_default_type')->value('value'))->toBe('zoom-in');
    expect(Setting::where('key', 'animation_duration_ms')->value('value'))->toBe('850');

    // Confirm public endpoint returns newly updated settings
    $publicResp = $this->getJson('/api/v1/animations');
    $publicResp->assertStatus(200);
    $publicResp->assertJsonPath('data.default_type', 'zoom-in');
    $publicResp->assertJsonPath('data.duration_ms', 850);
});

it('resets animation settings back to default factory configuration', function () {
    $admin = User::factory()->admin()->create();

    // First change a setting
    Setting::updateOrCreate(['key' => 'animation_default_type'], ['value' => 'flip-x', 'group' => 'animation']);

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/animations/reset');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.default_type', 'fade-up');
    $response->assertJsonPath('data.duration_ms', 700);

    expect(Setting::where('key', 'animation_default_type')->value('value'))->toBe('fade-up');
});

it('resolves section-level overrides and falls back to global settings appropriately', function () {
    $service = app(AnimationSettingsService::class);

    // Default global is fade-up
    $resolvedDefault = $service->resolveFor('home', 'unknown_section');
    expect($resolvedDefault['enabled'])->toBeTrue();
    expect($resolvedDefault['type'])->toBe('fade-up');

    // Home hero override is fade
    $resolvedHero = $service->resolveFor('home', 'hero');
    expect($resolvedHero['enabled'])->toBeTrue();
    expect($resolvedHero['type'])->toBe('fade');
    expect($resolvedHero['duration_ms'])->toBe(500);

    // Disable globally and check resolution
    $admin = User::factory()->admin()->create();
    $adminPayload = AnimationSettingsService::DEFAULT_SETTINGS;
    $adminPayload['enabled'] = false;
    $service->update($adminPayload, $admin);

    $resolvedWhenDisabled = $service->resolveFor('home', 'hero');
    expect($resolvedWhenDisabled['enabled'])->toBeFalse();
    expect($resolvedWhenDisabled['type'])->toBe('none');
});

it('persists and exposes hero image opacity settings', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/settings', [
        'settings' => [
            'hero_image_opacity' => '45',
            'hero_opacity' => '45',
        ],
    ]);

    $response->assertStatus(200);

    $publicResponse = $this->getJson('/api/v1/settings');
    $publicResponse->assertStatus(200);
    $publicResponse->assertJsonPath('data.hero_image_opacity', '45');
    $publicResponse->assertJsonPath('data.hero_opacity', '45');
});

it('persists and exposes public page hero settings', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/settings', [
        'settings' => [
            'page_hero_services_title' => 'Fresh cuts, every day',
            'page_hero_services_subtitle' => 'Find your next style.',
            'page_hero_services_overlay_opacity' => '68',
        ],
    ]);

    $response->assertStatus(200);

    $publicResponse = $this->getJson('/api/v1/settings');
    $publicResponse->assertStatus(200);
    $publicResponse->assertJsonPath('data.page_hero_services_title', 'Fresh cuts, every day');
    $publicResponse->assertJsonPath('data.page_hero_services_subtitle', 'Find your next style.');
    $publicResponse->assertJsonPath('data.page_hero_services_overlay_opacity', '68');
});

it('persists shop branding in general settings and exposes it publicly', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/settings', [
        'settings' => [
            'brand_name' => 'Sharp & Proper',
            'site_name' => 'Sharp & Proper',
            'brand_name_part_one' => 'Sharp',
            'brand_name_part_two' => 'Proper',
            'brand_name_separator' => 'space',
            'brand_gold_part' => 'first',
            'brand_tagline' => 'A better standard in grooming.',
            'site_description' => 'A better standard in grooming.',
        ],
    ]);

    $response->assertStatus(200);
    expect(Setting::where('key', 'brand_name')->value('group'))->toBe('general');
    expect(Setting::where('key', 'brand_tagline')->value('group'))->toBe('general');

    $this->getJson('/api/v1/settings')
        ->assertStatus(200)
        ->assertJsonPath('data.brand_name', 'Sharp & Proper')
        ->assertJsonPath('data.site_name', 'Sharp & Proper')
        ->assertJsonPath('data.brand_name_part_one', 'Sharp')
        ->assertJsonPath('data.brand_name_part_two', 'Proper')
        ->assertJsonPath('data.brand_name_separator', 'space')
        ->assertJsonPath('data.brand_gold_part', 'first')
        ->assertJsonPath('data.brand_tagline', 'A better standard in grooming.')
        ->assertJsonPath('data.site_description', 'A better standard in grooming.');
});

it('persists and exposes editable form placeholder settings', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->postJson('/api/v1/admin/settings', [
        'settings' => [
            'contact_form_name_placeholder' => 'Your name here',
            'contact_form_email_placeholder' => 'you@domain.com',
            'contact_form_phone_placeholder' => 'Your phone',
            'contact_form_message_placeholder' => 'Write your message',
            'account_deletion_email_placeholder' => 'Account email',
            'account_deletion_identifier_placeholder' => 'Account username',
            'account_deletion_reason_placeholder' => 'Tell us why',
        ],
    ])->assertStatus(200);

    expect(Setting::where('key', 'contact_form_name_placeholder')->value('group'))->toBe('contact');
    expect(Setting::where('key', 'account_deletion_email_placeholder')->value('group'))->toBe('legal');

    $this->getJson('/api/v1/settings')
        ->assertStatus(200)
        ->assertJsonPath('data.contact_form_name_placeholder', 'Your name here')
        ->assertJsonPath('data.contact_form_email_placeholder', 'you@domain.com')
        ->assertJsonPath('data.contact_form_phone_placeholder', 'Your phone')
        ->assertJsonPath('data.contact_form_message_placeholder', 'Write your message')
        ->assertJsonPath('data.account_deletion_email_placeholder', 'Account email')
        ->assertJsonPath('data.account_deletion_identifier_placeholder', 'Account username')
        ->assertJsonPath('data.account_deletion_reason_placeholder', 'Tell us why');
});

it('uploads and exposes a public page hero background image', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/api/v1/admin/settings', [
        'page_hero_page' => 'about',
        'page_hero_image' => UploadedFile::fake()->image('about.png'),
        'settings' => [
            'page_hero_about_title' => 'Our story',
            'page_hero_about_subtitle' => 'A sharper standard.',
            'page_hero_about_overlay_opacity' => '68',
        ],
    ]);

    $response->assertStatus(200);

    $setting = Setting::where('key', 'page_hero_about_image')->firstOrFail();
    expect($setting->group)->toBe('hero');
    expect(Storage::disk('public')->exists(str_replace('/storage/', '', $setting->value)))->toBeTrue();
    expect(Setting::where('key', 'page_hero_about_title')->value('value'))->toBe('Our story');
    expect(Setting::where('key', 'page_hero_about_subtitle')->value('value'))->toBe('A sharper standard.');
    expect(Setting::where('key', 'page_hero_about_overlay_opacity')->value('value'))->toBe('68');

    $this->getJson('/api/v1/settings')
        ->assertStatus(200)
        ->assertJsonPath('data.page_hero_about_image', $setting->value)
        ->assertJsonPath('data.page_hero_about_title', 'Our story')
        ->assertJsonPath('data.page_hero_about_subtitle', 'A sharper standard.')
        ->assertJsonPath('data.page_hero_about_overlay_opacity', '68');

    $this->actingAs($admin)->post('/api/v1/admin/settings', [
        'page_hero_page' => 'home',
        'page_hero_image' => UploadedFile::fake()->image('home.png'),
    ])->assertStatus(200);

    $homeImage = Setting::where('key', 'hero_image')->firstOrFail();
    expect($homeImage->group)->toBe('hero');
    expect(Storage::disk('public')->exists(str_replace('/storage/', '', $homeImage->value)))->toBeTrue();
});
