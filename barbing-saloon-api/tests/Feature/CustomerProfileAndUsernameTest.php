<?php

use App\Domain\Shared\Enums\UserRole;
use App\Models\User;
use Carbon\Carbon;

it('updates customer profile and syncs real_name with name', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'real_name' => 'Original Name',
        'role' => UserRole::customer,
    ]);

    $response = $this->actingAs($user)->postJson('/api/v1/customer/profile', [
        'name' => 'New Full Name',
        'email' => $user->email,
        'phone' => '+2348012345678',
        'bio' => 'Fade with sharp beard trim',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.name', 'New Full Name');
    $response->assertJsonPath('data.real_name', 'New Full Name');

    $user->refresh();
    expect($user->name)->toBe('New Full Name');
    expect($user->real_name)->toBe('New Full Name');
    expect($user->bio)->toBe('Fade with sharp beard trim');
});

it('returns 422 with field errors when email is already taken', function () {
    $existing = User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'myemail@example.com']);

    $response = $this->actingAs($user)->postJson('/api/v1/customer/profile', [
        'name' => 'John Doe',
        'email' => 'taken@example.com',
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    $response->assertJsonStructure([
        'success',
        'message',
        'errors' => ['email'],
    ]);
});

it('allows customer to set username for the first time and updates last_username_change_at', function () {
    $user = User::factory()->create([
        'username' => null,
        'last_username_change_at' => null,
    ]);

    $response = $this->actingAs($user)->postJson('/api/v1/customer/profile', [
        'name' => 'First User',
        'email' => $user->email,
        'username' => 'freshcut99',
    ]);

    $response->assertStatus(200);
    $user->refresh();
    expect($user->username)->toBe('freshcut99');
    expect($user->last_username_change_at)->not->toBeNull();
});

it('enforces 60-day cooldown on username changes', function () {
    $user = User::factory()->create([
        'username' => 'originaluser',
        'last_username_change_at' => Carbon::now()->subDays(15),
    ]);

    $response = $this->actingAs($user)->postJson('/api/v1/customer/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'username' => 'newuserhandle',
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('errors.username.0', 'Username can only be changed once every 60 days. 45 days remaining.');

    $user->refresh();
    expect($user->username)->toBe('originaluser');
});

it('allows username change after 60 days have passed', function () {
    $user = User::factory()->create([
        'username' => 'oldhandle',
        'last_username_change_at' => Carbon::now()->subDays(61),
    ]);

    $response = $this->actingAs($user)->postJson('/api/v1/customer/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'username' => 'updatedhandle',
    ]);

    $response->assertStatus(200);
    $user->refresh();
    expect($user->username)->toBe('updatedhandle');
});
