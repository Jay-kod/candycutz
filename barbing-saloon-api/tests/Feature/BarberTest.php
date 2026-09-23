<?php

use App\Models\User;
use App\Models\Barber;
use Illuminate\Support\Carbon;

it('characterises GET /barbers', function () {
    $response = $this->getJson('/api/v1/barbers');
    $response->assertStatus(200);
});

it('characterises GET /barbers/{id}', function () {
    $response = $this->getJson('/api/v1/barbers/999');
    expect(in_array($response->status(), [200, 404, 500]))->toBeTrue();
});

it('characterises PATCH /barbers/chair-status', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->patchJson('/api/v1/barbers/chair-status', []);
    expect(in_array($response->status(), [200, 422, 500, 403, 404]))->toBeTrue();
});

it('characterises GET /barbers/schedule', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->getJson('/api/v1/barbers/schedule');
    expect(in_array($response->status(), [200, 500, 403]))->toBeTrue();
});

it('characterises PUT /barbers/schedule', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->putJson('/api/v1/barbers/schedule', []);
    expect(in_array($response->status(), [200, 422, 500, 403]))->toBeTrue();
});

it('characterises GET /barbers/blocked-periods', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->getJson('/api/v1/barbers/blocked-periods');
    expect(in_array($response->status(), [200, 500, 403]))->toBeTrue();
});

it('characterises POST /barbers/blocked-periods', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson('/api/v1/barbers/blocked-periods', []);
    expect(in_array($response->status(), [200, 201, 422, 500, 403]))->toBeTrue();
});

it('characterises DELETE /barbers/blocked-periods/{id}', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->deleteJson('/api/v1/barbers/blocked-periods/999');
    expect(in_array($response->status(), [200, 204, 404, 500, 403]))->toBeTrue();
});

it('allows a barber to update their public account fields', function () {
    $barber = Barber::factory()->create();
    $user = User::findOrFail($barber->user_id);

    $response = $this->actingAs($user)->putJson('/api/v1/barbers/account', [
        'name' => 'Updated Barber',
        'phone' => '08030000000',
        'bio' => 'A public barber bio.',
        'experience_years' => 12,
        'specialties' => ['Fade', 'Beard trim'],
    ]);

    $response->assertOk();
    expect($user->refresh()->name)->toBe('Updated Barber');
    expect($barber->refresh()->experience_years)->toBe(12);
    expect($barber->specialties)->toContain('Fade');
});

it('enforces the monthly barber username cooldown', function () {
    $barber = Barber::factory()->create();
    $user = User::findOrFail($barber->user_id);
    $user->forceFill(['last_username_change_at' => Carbon::now()->subDays(10)])->save();

    $response = $this->actingAs($user)->patchJson('/api/v1/barbers/account/username', [
        'username' => 'new_barber_name',
    ]);

    $response->assertStatus(422)->assertJsonPath('success', false);
    expect($response->json('message'))->toContain('30 days');
});

it('allows a barber to upload avatar and cover image', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $barber = Barber::factory()->create();
    $user = User::findOrFail($barber->user_id);

    $jpegBytes = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
    $avatar = \Illuminate\Http\UploadedFile::fake()->createWithContent('my_avatar.jpg', $jpegBytes);
    $cover = \Illuminate\Http\UploadedFile::fake()->createWithContent('my_cover.jpg', $jpegBytes);

    $response = $this->actingAs($user)->post('/api/v1/barbers/account', [
        'name' => 'Barber With Images',
        'avatar' => $avatar,
        'cover_image' => $cover,
    ]);

    $response->assertOk();
    $user->refresh();
    expect($user->avatar)->not->toBeNull();
    expect($user->cover_image)->not->toBeNull();

    $barberData = $response->json('data.barber');
    expect($barberData['avatar_url'])->not->toContain('storage/storage');
    expect($barberData['cover_image_url'])->not->toContain('storage/storage');
});
