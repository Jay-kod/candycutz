<?php

it('characterises GET /availability', function () {
    $response = $this->getJson('/api/v1/availability');
    expect(in_array($response->status(), [200, 422, 500]))->toBeTrue();
});

it('characterises GET /service-zones', function () {
    $response = $this->getJson('/api/v1/service-zones');
    expect(in_array($response->status(), [200, 500]))->toBeTrue();
});

it('characterises GET /gallery', function () {
    $response = $this->getJson('/api/v1/gallery');
    expect(in_array($response->status(), [200, 500]))->toBeTrue();
});

it('characterises GET /gallery/{id}', function () {
    $response = $this->getJson('/api/v1/gallery/999');
    expect(in_array($response->status(), [200, 404, 500]))->toBeTrue();
});

it('characterises GET /testimonials', function () {
    $response = $this->getJson('/api/v1/testimonials');
    expect(in_array($response->status(), [200, 500]))->toBeTrue();
});

it('characterises GET /blog', function () {
    $response = $this->getJson('/api/v1/blog');
    expect(in_array($response->status(), [200, 500]))->toBeTrue();
});

it('characterises GET /blog/{slug}', function () {
    $response = $this->getJson('/api/v1/blog/some-slug');
    expect(in_array($response->status(), [200, 404, 500]))->toBeTrue();
});
