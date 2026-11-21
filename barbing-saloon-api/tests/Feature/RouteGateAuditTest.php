<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;

it('verifies that all non-whitelisted routes enforce authentication and gates', function () {
    $exitCode = Artisan::call('routes:audit', ['--fail-on-violation' => true]);

    expect($exitCode)->toBe(0);
});

it('verifies the public route whitelist only permits expected unauthenticated routes', function () {
    $whitelist = config('public_routes.routes', []);
    expect($whitelist)->toBeArray();
    expect($whitelist)->toContain('api/v1/health');
    expect($whitelist)->toContain('api/v1/auth/login');
    expect($whitelist)->toContain('api/v1/auth/register');

    // Ensure sensitive admin endpoints are NEVER in the public whitelist
    foreach ($whitelist as $uri) {
        expect($uri)->not->toContain('api/v1/admin');
        expect($uri)->not->toContain('api/v1/super-admin');
        expect($uri)->not->toContain('api/v1/superadmin');
    }
});
