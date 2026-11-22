<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

it('enforces hardened HTTP security headers on all responses', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    $response->assertHeader('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'; base-uri 'none'");
    $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
    $response->assertHeader('Cross-Origin-Resource-Policy', 'cross-origin');
    $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

it('blocks automated dotfile scanning probes (.env, .git, .aws)', function () {
    Cache::forget('security_probes_blocked_total');

    $probes = [
        '/.env',
        '/.env.local',
        '/.git/HEAD',
        '/.git/config',
        '/.aws/credentials',
    ];

    foreach ($probes as $uri) {
        $res = $this->get($uri);
        $res->assertStatus(400);
        $res->assertJsonPath('error.code', 'MALICIOUS_PROBE_BLOCKED');
    }

    expect((int) Cache::get('security_probes_blocked_total', 0))->toBeGreaterThanOrEqual(count($probes));
});

it('blocks vulnerability scanner scripts (wp-admin, phpmyadmin, phpinfo)', function () {
    $scannerUris = [
        '/wp-admin',
        '/wp-login.php',
        '/xmlrpc.php',
        '/phpmyadmin/index.php',
        '/phpinfo.php',
    ];

    foreach ($scannerUris as $uri) {
        $res = $this->get($uri);
        $res->assertStatus(400);
        $res->assertJsonPath('error.code', 'MALICIOUS_PROBE_BLOCKED');
    }
});

it('blocks directory traversal attempts in path and decoded sequences', function () {
    $traversals = [
        '/api/v1/services/..%2f..%2fetc%2fpasswd',
        '/api/v1/barbers/%2e%2e/%2e%2e/windows/win.ini',
    ];

    foreach ($traversals as $uri) {
        $res = $this->get($uri);
        $res->assertStatus(400);
        $res->assertJsonPath('error.code', 'MALICIOUS_PROBE_BLOCKED');
    }
});

it('blocks SQL injection and script exploit patterns in raw query parameters', function () {
    // 1. SQL injection UNION SELECT attempt in query
    $sqli = $this->get('/api/v1/services?query=1%20union%20select%201,2,3');
    $sqli->assertStatus(400);
    $sqli->assertJsonPath('error.code', 'MALICIOUS_QUERY_BLOCKED');

    // 2. Cross-site scripting probe in query
    $xss = $this->get('/api/v1/services?tag=%3Cscript%3Ealert(1)%3C/script%3E');
    $xss->assertStatus(400);
    $xss->assertJsonPath('error.code', 'MALICIOUS_QUERY_BLOCKED');

    // 3. System file probe in query
    $lfi = $this->get('/api/v1/services?file=etc/passwd');
    $lfi->assertStatus(400);
    $lfi->assertJsonPath('error.code', 'MALICIOUS_QUERY_BLOCKED');
});

it('blocks disallowed HTTP verbs with 405 Method Not Allowed', function () {
    $response = $this->call('TRACE', '/api/v1/health');
    $response->assertStatus(405);
    $response->assertJsonPath('error.code', 'METHOD_NOT_ALLOWED');
});

it('enforces dual-key rate limiting preventing credential stuffing sweeps across different identities', function () {
    $ip = '127.0.0.1';
    RateLimiter::clear('auth-ip:'.$ip);

    // Simulate an attacker rotating through 30 unique usernames from the same IP address
    for ($i = 1; $i <= 30; $i++) {
        $uniqueIdentity = "victim_{$i}@candycutz.ng";
        RateLimiter::clear("{$uniqueIdentity}|{$ip}");

        $this->postJson('/api/v1/auth/login', [
            'identity' => $uniqueIdentity,
            'password' => 'wrong-pass',
        ]);
    }

    // 31st attempt with yet another new identity from the same IP must be throttled by the IP gate
    $sweptRes = $this->postJson('/api/v1/auth/login', [
        'identity' => 'new_victim_31@candycutz.ng',
        'password' => 'wrong-pass',
    ]);

    $sweptRes->assertStatus(429);
    $sweptRes->assertJsonPath('error.code', 'RATE_LIMIT_EXCEEDED');
});

it('allows legitimate API traffic to proceed without interference', function () {
    $res = $this->getJson('/api/v1/health');
    $res->assertOk();
    $res->assertJsonPath('success', true);

    $catRes = $this->getJson('/api/v1/services');
    $catRes->assertOk();
    $catRes->assertJsonPath('success', true);
});
