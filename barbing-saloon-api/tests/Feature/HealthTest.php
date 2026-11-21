<?php

it('returns the versioned API health envelope', function () {
    $response = $this->getJson('/api/v1/health');

    $response
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'status',
                'timestamp',
                'services' => [
                    'database',
                    'storage',
                    'api_version',
                    'flagship',
                ],
            ],
        ]);
});

it('returns production security headers on API responses', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    $response->assertHeader('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'; base-uri 'none'");
});
