<?php

use App\Domain\Shared\Enums\BlogStatus;
use App\Models\BlogPost;
use App\Models\User;

it('persists blog fields and publication changes from the admin editor', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $post = BlogPost::factory()->create([
        'author_id' => $admin->id,
        'featured_image' => 'uploads/blog/cover.jpg',
    ]);

    $response = $this->actingAs($admin)->putJson("/api/v1/admin/blog/{$post->id}", [
        'title' => 'Updated grooming guide',
        'excerpt' => null,
        'content' => '<p>Updated article content.</p>',
        'author_display' => 'CandyCutz Editorial',
        'status' => 'published',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Updated grooming guide')
        ->assertJsonPath('data.author_display', 'CandyCutz Editorial');

    $post->refresh();
    expect($post->title)->toBe('Updated grooming guide')
        ->and($post->body)->toContain('Updated article content.')
        ->and($post->excerpt)->toContain('Updated article content.')
        ->and($post->author_display)->toBe('CandyCutz Editorial')
        ->and($post->featured_image)->toBe('uploads/blog/cover.jpg')
        ->and($post->status)->toBe(BlogStatus::published)
        ->and($post->published_at)->not->toBeNull();

    $this->actingAs($admin)->putJson("/api/v1/admin/blog/{$post->id}", [
        'status' => 'draft',
    ])->assertOk();

    expect($post->fresh()->status)->toBe(BlogStatus::draft)
        ->and($post->fresh()->published_at)->toBeNull();
});

it('limits barber editing to their own posts and only exposes published posts to customers', function () {
    $barber = User::factory()->create(['role' => 'barber']);
    $otherBarber = User::factory()->create(['role' => 'barber']);
    $customer = User::factory()->create(['role' => 'customer']);

    $ownPublished = BlogPost::factory()->published()->create([
        'author_id' => $barber->id,
    ]);
    $otherPublished = BlogPost::factory()->published()->create([
        'author_id' => $otherBarber->id,
    ]);
    $ownDraft = BlogPost::factory()->create([
        'author_id' => $barber->id,
    ]);
    $otherDraft = BlogPost::factory()->create([
        'author_id' => $otherBarber->id,
    ]);

    $barberList = $this->actingAs($barber)->getJson('/api/v1/barber/blog')->assertOk();
    $barberIds = collect($barberList->json('data'))->pluck('id');
    expect($barberIds)->toContain($ownPublished->id)
        ->toContain($ownDraft->id)
        ->not->toContain($otherDraft->id)
        ->not->toContain($otherPublished->id);

    $customerList = $this->actingAs($customer)->getJson('/api/v1/blog')->assertOk();
    $customerIds = collect($customerList->json('data'))->pluck('id');
    expect($customerIds)->toContain($ownPublished->id)
        ->toContain($otherPublished->id)
        ->not->toContain($ownDraft->id)
        ->not->toContain($otherDraft->id);

    $this->actingAs($customer)->getJson("/api/v1/blog/{$ownDraft->slug}")->assertNotFound();
    $this->actingAs($barber)->getJson("/api/v1/blog/{$ownDraft->slug}")->assertOk();
    $this->actingAs($barber)->getJson("/api/v1/blog/{$otherDraft->slug}")->assertNotFound();

    $this->actingAs($barber)->postJson("/api/v1/barber/blog/{$otherDraft->id}", [
        'title' => 'Attempted edit',
    ])->assertForbidden();

    $this->actingAs($customer)->putJson("/api/v1/admin/blog/{$ownPublished->id}", [
        'title' => 'Customer edit attempt',
    ])->assertForbidden();
});

it('persists barber-created blog posts as drafts in the database', function () {
    $barber = User::factory()->create(['role' => 'barber']);

    $response = $this->actingAs($barber)->postJson('/api/v1/barber/blog', [
        'title' => 'A barber authored article',
        'content' => '<p>Advice from a barber.</p>',
        'excerpt' => 'A short summary',
        'is_published' => 0,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'draft');

    $this->assertDatabaseHas('blog_posts', [
        'author_id' => $barber->id,
        'title' => 'A barber authored article',
        'body' => '<p>Advice from a barber.</p>',
        'excerpt' => 'A short summary',
        'status' => 'draft',
    ]);

    $postId = $response->json('data.id');
    $this->actingAs($barber)->postJson("/api/v1/barber/blog/{$postId}", [
        'is_published' => 1,
    ])->assertOk()
        ->assertJsonPath('data.status', 'published');

    expect(BlogPost::findOrFail($postId)->published_at)->not->toBeNull();
});
