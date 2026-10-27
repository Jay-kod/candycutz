<?php

use App\Domain\Shared\Enums\UserRole;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

it('stores customer avatars privately and serves them only through a signed URL', function () {
    Storage::fake('local');
    Storage::fake('public');

    $user = User::factory()->customer()->create();
    $jpegBytes = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
    $avatar = UploadedFile::fake()->createWithContent('customer.jpg', $jpegBytes);

    $response = $this->actingAs($user)->post('/api/v1/account/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $avatar,
    ]);

    $response->assertOk();
    $storedPath = $user->fresh()->avatar;
    expect($storedPath)->toStartWith("user-media/{$user->id}/");
    Storage::disk('local')->assertExists($storedPath);
    Storage::disk('public')->assertMissing($storedPath);

    $signedUrl = $response->json('data.avatar');
    expect($signedUrl)->toContain('signature=');
    $this->get($signedUrl)->assertOk();
    $this->get($signedUrl.'&signature=invalid')->assertForbidden();
    $this->get('/storage/'.$storedPath)->assertForbidden();

    $testimonial = Testimonial::factory()->create([
        'customer_id' => $user->id,
        'client_avatar' => $storedPath,
    ]);
    expect((new TestimonialResource($testimonial))->resolve()['avatar_url'])->toContain('signature=');
});

it('dry-runs legacy account media migration without changing files or user paths', function () {
    Storage::fake('local');
    Storage::fake('public');
    $user = User::factory()->customer()->create(['avatar' => '/storage/uploads/avatars/legacy.jpg']);
    Storage::disk('public')->put('uploads/avatars/legacy.jpg', 'legacy-avatar-bytes');

    $this->artisan('user-media:privatize')->assertSuccessful();

    expect($user->fresh()->avatar)->toBe('/storage/uploads/avatars/legacy.jpg');
    Storage::disk('public')->assertExists('uploads/avatars/legacy.jpg');
    Storage::disk('local')->assertMissing("user-media/{$user->id}/avatar/legacy.jpg");
});

it('moves legacy account media privately and updates linked testimonial references', function () {
    Storage::fake('local');
    Storage::fake('public');
    $user = User::factory()->customer()->create(['avatar' => '/storage/uploads/avatars/legacy.jpg']);
    $testimonial = Testimonial::factory()->create([
        'customer_id' => $user->id,
        'client_avatar' => '/storage/uploads/avatars/legacy.jpg',
    ]);
    Storage::disk('public')->put('uploads/avatars/legacy.jpg', 'legacy-avatar-bytes');

    $this->artisan('user-media:privatize --apply')->assertSuccessful();

    $privatePath = "user-media/{$user->id}/avatar/legacy.jpg";
    expect($user->fresh()->avatar)->toBe($privatePath);
    expect($testimonial->fresh()->client_avatar)->toBe($privatePath);
    Storage::disk('local')->assertExists($privatePath);
    Storage::disk('public')->assertMissing('uploads/avatars/legacy.jpg');
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
