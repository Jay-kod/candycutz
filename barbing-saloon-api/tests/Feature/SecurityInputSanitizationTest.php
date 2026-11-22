<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BlogPost;
use App\Models\FeedbackSubmission;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\InputSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('strips malicious HTML and script tags from review comments', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $barberUser = User::factory()->create(['role' => 'barber']);
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);

    $response = $this->actingAs($customer)->postJson('/api/v1/testimonials', [
        'rating' => 5,
        'comment' => 'Amazing haircut! <script>alert("stored-xss")</script>',
        'barber_id' => $barber->id,
    ]);

    $response->assertCreated();
    $saved = Testimonial::where('customer_id', $customer->id)->first();
    expect($saved)->not->toBeNull();
    expect($saved->review)->toBe('Amazing haircut!');
    expect($saved->review)->not->toContain('<script>');
    expect($saved->review)->not->toContain('alert');
});

it('strips dangerous event handlers and img onerror vectors from feedback submissions', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($customer)->postJson('/api/v1/customer/feedback', [
        'category' => 'improvement',
        'title' => 'Dark mode issue <script>bad()</script>',
        'description' => 'Found an issue with dark mode <img src=x onerror=document.location="http://evil.com">',
    ]);

    $response->assertCreated();
    $submission = FeedbackSubmission::where('user_id', $customer->id)->first();
    expect($submission)->not->toBeNull();
    expect($submission->title)->toBe('Dark mode issue');
    expect($submission->description)->toBe('Found an issue with dark mode');
    expect($submission->description)->not->toContain('<img');
    expect($submission->description)->not->toContain('onerror');
});

it('strips HTML tags and script injections from user profile updates', function () {
    $customer = User::factory()->create([
        'role' => 'customer',
        'name' => 'Original Name',
        'email' => 'sanitize_profile@candycutz.ng',
    ]);

    $response = $this->actingAs($customer)->putJson('/api/v1/account/profile', [
        'name' => 'John <b onmouseover=alert(1)>Doe</b>',
        'email' => 'sanitize_profile@candycutz.ng',
        'phone' => '+234 801 234 5678',
    ]);

    $response->assertOk();
    $customer->refresh();
    expect($customer->name)->toBe('John Doe');
    expect($customer->name)->not->toContain('<b>');
    expect($customer->name)->not->toContain('onmouseover');
});

it('preserves safe formatting in rich text blog body while purging malicious script tags', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->postJson('/api/v1/blog', [
        'title' => 'Top 5 Cuts <script>bad()</script>',
        'body' => '<h2>Sharp Fades</h2><p>Always maintain your <strong>fade</strong>.<script>alert("xss")</script></p>',
        'tags' => ['grooming', 'trends'],
    ]);

    $response->assertCreated();
    $post = BlogPost::latest('id')->first();
    expect($post)->not->toBeNull();
    expect($post->title)->toBe('Top 5 Cuts');
    expect($post->title)->not->toContain('<script>');
    expect($post->body)->toContain('<h2>Sharp Fades</h2>');
    expect($post->body)->toContain('<strong>fade</strong>');
    expect($post->body)->not->toContain('<script>');
});

it('preserves password integrity without altering special characters or hashes', function () {
    $rawPassword = 'My<Secure>P@ssw0rd!#';

    $registerRes = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'raw_pass_test@candycutz.ng',
        'phone' => '+234 809 111 2233',
        'password' => $rawPassword,
        'password_confirmation' => $rawPassword,
        'role' => 'customer',
    ]);

    $registerRes->assertCreated();

    // Verify user can login with exact unstripped password containing < and >
    $loginRes = $this->postJson('/api/v1/auth/login', [
        'identity' => 'raw_pass_test@candycutz.ng',
        'password' => $rawPassword,
    ]);

    $loginRes->assertOk();
    $loginRes->assertJsonPath('success', true);
});

it('cleans null bytes and control characters gracefully via InputSanitizer utility', function () {
    $dirty = "Hello\0 World\x07 with control \x1F characters <iframe src=evil.com></iframe>";
    $cleaned = InputSanitizer::cleanPlainText($dirty);

    expect($cleaned)->toBe('Hello World with control  characters');
    expect($cleaned)->not->toContain("\0");
    expect($cleaned)->not->toContain('<iframe');
});
