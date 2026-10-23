<?php

use App\Http\Requests\Api\V1\Admin\UpdateSettingsRequest;
use App\Models\Setting;

it('groups homepage CTA settings with public hero settings', function () {
    expect(Setting::resolveGroupForKey('cta_title'))->toBe('hero');
    expect(Setting::resolveGroupForKey('page_hero_services_title'))->toBe('hero');
    expect(Setting::resolveGroupForKey('page_hero_about_image'))->toBe('hero');
});

it('groups editable legal and cookie pages as public legal settings', function () {
    expect(Setting::resolveGroupForKey('legal_stuff_title'))->toBe('legal');
    expect(Setting::resolveGroupForKey('security_sections'))->toBe('legal');
    expect(Setting::resolveGroupForKey('cookie_analytics_description'))->toBe('legal');
});

it('validates page hero uploads and restricts them to public content pages', function () {
    $rules = (new UpdateSettingsRequest)->rules();

    expect($rules['page_hero_page'])
        ->toContain('required_with:page_hero_image')
        ->toContain('in:home,about,services,gallery,contact,privacy,terms,legal,security,cookies,account_deletion');
    expect($rules['page_hero_image'])
        ->toContain('image')
        ->toContain('mimes:jpeg,png,webp')
        ->toContain('max:5120');
});

it('groups animation settings with animation group', function () {
    expect(Setting::resolveGroupForKey('animation_default_type'))->toBe('animation');
    expect(Setting::resolveGroupForKey('animation_enabled'))->toBe('animation');
    expect(Setting::resolveGroupForKey('animation_overrides'))->toBe('animation');
});

it('groups shop identity settings as public general settings and validates their lengths', function () {
    expect(Setting::resolveGroupForKey('brand_name'))->toBe('general');
    expect(Setting::resolveGroupForKey('site_name'))->toBe('general');
    expect(Setting::resolveGroupForKey('brand_tagline'))->toBe('general');

    $adminRules = (new UpdateSettingsRequest)->rules();
    $superAdminRules = (new \App\Http\Requests\Api\V1\SuperAdmin\UpdateSettingsRequest)->rules();

    foreach ([$adminRules, $superAdminRules] as $rules) {
        expect($rules['settings.brand_name'])->toContain('max:120');
        expect($rules['settings.site_name'])->toContain('max:120');
        expect($rules['settings.brand_name_part_one'])->toContain('max:60');
        expect($rules['settings.brand_name_part_two'])->toContain('max:60');
        expect($rules['settings.brand_name_separator'])->toContain('in:none,space');
        expect($rules['settings.brand_gold_part'])->toContain('in:first,second');
        expect($rules['settings.brand_tagline'])->toContain('max:300');
    }
});
