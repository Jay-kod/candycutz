<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns only safe account security activities belonging to the customer', function () {
    $customer = User::factory()->customer()->create();
    $otherCustomer = User::factory()->customer()->create();

    AuditLog::create([
        'user_id' => null,
        'user_role' => 'system',
        'action' => 'auth.login',
        'module' => 'AuthenticateUser',
        'target_type' => User::class,
        'target_id' => $customer->id,
        'new_value' => [
            'email' => $customer->email,
            'device' => 'web:browser',
            'private_marker' => 'must-not-be-returned',
        ],
        'ip_address' => '192.0.2.10',
        'user_agent' => 'private user agent',
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    AuditLog::create([
        'user_id' => $customer->id,
        'user_role' => 'customer',
        'action' => 'auth.change_password',
        'module' => 'ChangePassword',
        'target_type' => User::class,
        'target_id' => $customer->id,
        'new_value' => ['private_marker' => 'must-not-be-returned'],
    ]);

    AuditLog::create([
        'user_id' => null,
        'user_role' => 'system',
        'action' => 'auth.login',
        'module' => 'AuthenticateUser',
        'target_type' => User::class,
        'target_id' => $otherCustomer->id,
    ]);

    $response = $this->actingAs($customer)->getJson('/api/v1/customer/dashboard');

    $response->assertOk()
        ->assertJsonCount(2, 'data.account_activities');

    $activities = $response->json('data.account_activities');
    expect(collect($activities)->pluck('title')->all())
        ->toContain('Signed in', 'Password changed');
    expect(json_encode($activities))
        ->not->toContain('must-not-be-returned')
        ->not->toContain('192.0.2.10')
        ->not->toContain('private user agent');
});

it('records changed profile field names without exposing the new values', function () {
    $customer = User::factory()->customer()->create([
        'name' => 'Original Customer',
        'phone' => null,
    ]);

    $this->actingAs($customer)->postJson('/api/v1/customer/profile', [
        'name' => 'Updated Customer',
        'email' => $customer->email,
        'phone' => '+2348012345678',
        'bio' => 'New profile details',
    ])->assertOk();

    $profileActivity = AuditLog::query()
        ->where('user_id', $customer->id)
        ->where('action', 'profile.updated')
        ->latest()
        ->firstOrFail();

    expect($profileActivity->new_value['changed_fields'])
        ->toContain('name', 'phone', 'bio')
        ->not->toContain('Updated Customer', '+2348012345678', 'New profile details');

    $dashboardResponse = $this->actingAs($customer)->getJson('/api/v1/customer/dashboard');
    expect($dashboardResponse->json('data.account_activities.0.title'))->toBe('Account details updated');
    expect($dashboardResponse->json('data.account_activities.0.message'))->toBe('Updated name, phone number, profile details.');
});
