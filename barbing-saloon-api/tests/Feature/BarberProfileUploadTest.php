<?php

use App\Models\Barber;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('stores a barber avatar privately through the account endpoint', function () {
    Storage::fake('local');
    Storage::fake('public');

    $barber = Barber::factory()->create();
    $avatar = UploadedFile::fake()->image('barber.jpg', 800, 600);
    $cover = UploadedFile::fake()->image('cover.jpg', 1600, 900);

    $response = $this->actingAs($barber->user)->post('/api/v1/barber/account', [
        'name' => $barber->user->name,
        'email' => $barber->user->email,
        'avatar' => $avatar,
        'cover_image' => $cover,
    ]);

    $response->assertOk();

    $storedPath = $barber->user->fresh()->avatar;
    expect($storedPath)->toStartWith("user-media/{$barber->user_id}/avatar/");
    Storage::disk('local')->assertExists($storedPath);
    Storage::disk('public')->assertMissing($storedPath);
    $storedCoverPath = $barber->user->fresh()->cover_image;
    expect($storedCoverPath)->toStartWith("user-media/{$barber->user_id}/cover/");
    Storage::disk('local')->assertExists($storedCoverPath);
    Storage::disk('public')->assertMissing($storedCoverPath);
    expect($response->json('data.user.avatar'))->toContain('signature=');
    expect($response->json('data.user.avatar_url'))->toContain('signature=');
    expect($response->json('data.user.cover_image'))->toContain('signature=');
    expect($response->json('data.user.cover_image_url'))->toContain('signature=');
    expect($response->json('data.barber.avatar'))->toContain('signature=');
    expect($response->json('data.barber.avatar_url'))->toContain('signature=');
    expect($response->json('data.barber.cover_image'))->toContain('signature=');
    expect($response->json('data.barber.cover_image_url'))->toContain('signature=');
});

it('removes a barber avatar when remove_avatar flag is set', function () {
    Storage::fake('local');
    Storage::fake('public');

    $barber = Barber::factory()->create();
    $barber->user->update(['avatar' => "user-media/{$barber->user_id}/avatar/existing.webp"]);

    $response = $this->actingAs($barber->user)->post('/api/v1/barber/account', [
        'remove_avatar' => true,
    ]);

    $response->assertOk();
    expect($barber->user->fresh()->avatar)->toBeNull();
    expect($response->json('data.user.avatar'))->toBeNull();
    expect($response->json('data.user.avatar_url'))->toBeNull();
});
