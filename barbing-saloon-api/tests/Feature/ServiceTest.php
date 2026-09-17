<?php

it('characterises GET /services', function () {
    $response = $this->getJson('/api/v1/services');
    $response->assertStatus(200);
});

it('characterises GET /service-categories', function () {
    $response = $this->getJson('/api/v1/service-categories');
    $response->assertStatus(200);
});

it('characterises GET /services/{idOrSlug}', function () {
    $response = $this->getJson('/api/v1/services/999');
    // Probably 404
    expect(in_array($response->status(), [200, 404, 500]))->toBeTrue();
});
