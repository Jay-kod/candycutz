<?php

declare(strict_types=1);

use App\Domain\Shared\Enums\UserRole;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

it('allows admin to list customers with is_active and status', function () {
    $admin = User::factory()->create(['role' => UserRole::admin]);
    $customer = User::factory()->create([
        'name' => 'Alice Test',
        'email' => 'alice@test.com',
        'role' => UserRole::customer,
        'is_active' => true,
        'status' => 'active',
    ]);

    $response = $this->actingAs($admin)->getJson('/api/v1/admin/customers');

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $data = collect($response->json('data'));
    $found = $data->firstWhere('id', $customer->id);
    expect($found)->not->toBeNull();
    expect($found['name'])->toBe('Alice Test');
    expect($found['is_active'])->toBeTrue();
    expect($found['status'])->toBe('active');
});

it('allows admin to create a new customer', function () {
    $admin = User::factory()->create(['role' => UserRole::admin]);

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/customers', [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'phone' => '+2348011223344',
        'password' => 'CustomerSecret123!',
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.name', 'John Doe');
    $response->assertJsonPath('data.email', 'john.doe@example.com');
    $response->assertJsonPath('data.phone', '+2348011223344');

    $customer = User::where('email', 'john.doe@example.com')->first();
    expect($customer)->not->toBeNull();
    expect($customer->role)->toBe(UserRole::customer);
    expect($customer->is_active)->toBeTrue();
    expect(Hash::check('CustomerSecret123!', $customer->password))->toBeTrue();
});

it('allows admin to update customer details', function () {
    $admin = User::factory()->create(['role' => UserRole::admin]);
    $customer = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old.email@example.com',
        'phone' => '08000000000',
        'role' => UserRole::customer,
    ]);

    $response = $this->actingAs($admin)->putJson("/api/v1/admin/customers/{$customer->id}", [
        'name' => 'Updated Name',
        'email' => 'updated.email@example.com',
        'phone' => '08099999999',
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.name', 'Updated Name');
    $response->assertJsonPath('data.email', 'updated.email@example.com');
    $response->assertJsonPath('data.phone', '08099999999');

    $customer->refresh();
    expect($customer->name)->toBe('Updated Name');
    expect($customer->email)->toBe('updated.email@example.com');
});

it('allows admin to suspend and activate customer account', function () {
    $admin = User::factory()->create(['role' => UserRole::admin]);
    $customer = User::factory()->create([
        'role' => UserRole::customer,
        'is_active' => true,
        'status' => 'active',
    ]);

    // Suspend
    $suspendRes = $this->actingAs($admin)->patchJson("/api/v1/admin/customers/{$customer->id}/status", [
        'is_active' => false,
    ]);
    $suspendRes->assertOk();
    $suspendRes->assertJsonPath('data.is_active', false);
    $suspendRes->assertJsonPath('data.status', 'suspended');

    $customer->refresh();
    expect($customer->is_active)->toBeFalse();
    expect($customer->status)->toBe('suspended');

    // Activate
    $activateRes = $this->actingAs($admin)->patchJson("/api/v1/admin/customers/{$customer->id}/status", [
        'is_active' => true,
    ]);
    $activateRes->assertOk();
    $activateRes->assertJsonPath('data.is_active', true);
    $activateRes->assertJsonPath('data.status', 'active');

    $customer->refresh();
    expect($customer->is_active)->toBeTrue();
    expect($customer->status)->toBe('active');
});

it('allows admin to reset customer password', function () {
    $admin = User::factory()->create(['role' => UserRole::admin]);
    $customer = User::factory()->create([
        'role' => UserRole::customer,
        'password' => Hash::make('OldPassword123!'),
    ]);

    $response = $this->actingAs($admin)->postJson("/api/v1/admin/customers/{$customer->id}/reset-password", [
        'password' => 'NewSecurePassword999!',
    ]);

    $response->assertOk();
    $customer->refresh();
    expect(Hash::check('NewSecurePassword999!', $customer->password))->toBeTrue();
});

it('allows admin to soft-delete customer account', function () {
    $admin = User::factory()->create(['role' => UserRole::admin]);
    $customer = User::factory()->create([
        'role' => UserRole::customer,
    ]);

    $response = $this->actingAs($admin)->deleteJson("/api/v1/admin/customers/{$customer->id}");

    $response->assertOk();
    expect(User::find($customer->id))->toBeNull();
    expect(User::withTrashed()->find($customer->id))->not->toBeNull();
});

it('forbids regular customers from customer management endpoints', function () {
    $customer = User::factory()->create(['role' => UserRole::customer]);
    $targetCustomer = User::factory()->create(['role' => UserRole::customer]);

    $this->actingAs($customer)->getJson('/api/v1/admin/customers')->assertForbidden();
    $this->actingAs($customer)->deleteJson("/api/v1/admin/customers/{$targetCustomer->id}")->assertForbidden();
});

it('returns a structured validation response when Paystack is not configured', function () {
    $admin = User::factory()->create(['role' => UserRole::admin]);

    Setting::query()->where('key', 'paystack_secret_key')->delete();
    config()->set('services.paystack.secret_key', null);

    $response = $this->actingAs($admin)->postJson('/api/v1/admin/paystack/test-connection');

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('message', 'Paystack Secret Key is not configured. Please enter or save your secret key first.');
});

