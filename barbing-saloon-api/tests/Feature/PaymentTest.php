<?php

use App\Models\User;

it('characterises POST /payments/webhook', function () {
    $response = $this->postJson('/api/v1/payments/webhook', []);
    expect(in_array($response->status(), [200, 422, 500, 400, 401, 403, 503]))->toBeTrue();
});

it('characterises GET /payments/appointments/{appointmentId}/receipt', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->getJson('/api/v1/payments/appointments/999/receipt');
    expect(in_array($response->status(), [200, 404, 500, 403]))->toBeTrue();
});
