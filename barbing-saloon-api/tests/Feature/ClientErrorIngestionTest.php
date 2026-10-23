<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ErrorEvent;
use App\Models\ErrorGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('ingests a single client-reported error event', function () {
    $payload = [
        'event_id' => 'evt_'.uniqid(),
        'source' => 'client_web',
        'level' => 'error',
        'name' => 'TypeError',
        'message' => 'Cannot read properties of undefined (reading "name")',
        'stack' => "TypeError: Cannot read properties\n    at BookingModal.vue:42",
        'error_code' => 'JS_RUNTIME_ERROR',
        'http_status' => 0,
        'request_id' => 'req_test12345678',
        'route' => '/book/confirm',
        'app_version' => '1.2.0',
        'platform' => 'Chrome 120.0 Windows',
        'device' => 'Desktop',
        'network_state' => 'online',
        'occurred_at' => now()->toIso8601String(),
        'breadcrumbs' => [
            ['type' => 'navigation', 'to' => '/book/confirm'],
            ['type' => 'xhr', 'url' => '/api/v1/appointments', 'status' => 500, 'request_id' => 'req_test12345678'],
        ],
    ];

    $response = $this->postJson('/api/v1/system/client-errors', $payload);

    $response->assertCreated();
    expect($response->json('data.ingested_count'))->toBe(1);

    expect(ErrorGroup::query()->where('error_code', 'JS_RUNTIME_ERROR')->exists())->toBeTrue();
    $event = ErrorEvent::query()->where('request_id', 'req_test12345678')->first();
    expect($event)->not->toBeNull();
    expect($event->client)->toBe('client_web');
    expect($event->route_uri)->toBe('/book/confirm');
});

it('enforces idempotency on duplicate client event_id', function () {
    $eventId = 'evt_duplicate_'.uniqid();
    $payload = [
        'event_id' => $eventId,
        'source' => 'client_mobile',
        'level' => 'error',
        'name' => 'NetworkError',
        'message' => 'Network request failed',
        'error_code' => 'NETWORK_UNREACHABLE',
    ];

    $response1 = $this->postJson('/api/v1/system/client-errors', $payload);
    $response1->assertCreated();

    $response2 = $this->postJson('/api/v1/system/client-errors', $payload);
    $response2->assertCreated();

    // Exactly one event should be stored despite two posts
    $matchingEvents = ErrorEvent::query()
        ->where('context->event_id', $eventId)
        ->count();
    expect($matchingEvents)->toBe(1);
});

it('ingests batch of client-reported error events capped at 20', function () {
    $events = [];
    for ($i = 0; $i < 25; $i++) {
        $events[] = [
            'event_id' => 'evt_batch_'.$i.'_'.uniqid(),
            'source' => 'client_web',
            'level' => 'warning',
            'name' => 'WarningEvent',
            'message' => 'Slow render detected',
            'error_code' => 'CLIENT_PERF_WARNING',
        ];
    }

    $response = $this->postJson('/api/v1/system/client-errors', ['events' => $events]);
    $response->assertCreated();
    // Cap is 20
    expect($response->json('data.ingested_count'))->toBe(20);
});
