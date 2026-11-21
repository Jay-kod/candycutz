<?php

use App\Models\FeedbackSubmission;
use App\Models\User;

it('requires a signed-in customer to submit feedback', function () {
    $this->postJson('/api/v1/customer/feedback', [
        'category' => 'feature_request',
        'title' => 'Add online waitlist',
        'description' => 'Let customers join the queue before arriving.',
    ])->assertUnauthorized();

    $barber = User::factory()->barber()->create();

    $this->actingAs($barber)->postJson('/api/v1/customer/feedback', [
        'category' => 'feature_request',
        'title' => 'Add online waitlist',
        'description' => 'Let customers join the queue before arriving.',
    ])->assertForbidden();

    expect(FeedbackSubmission::count())->toBe(0);
});

it('stores a customer feedback submission privately for admin review', function () {
    $customer = User::factory()->customer()->create();

    $response = $this->actingAs($customer)->postJson('/api/v1/customer/feedback', [
        'category' => 'maintenance',
        'title' => 'Booking page loading issue',
        'description' => 'The booking page takes a long time to load on my phone.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.category', 'maintenance')
        ->assertJsonPath('data.status', 'new');

    $submission = FeedbackSubmission::firstOrFail();
    expect($submission->user_id)->toBe($customer->id);

    $this->actingAs($customer)
        ->getJson('/api/v1/admin/feedback')
        ->assertForbidden();
});

it('validates feedback fields and available categories', function () {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer)->postJson('/api/v1/customer/feedback', [
        'category' => 'remove',
        'title' => '',
        'description' => '',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['category', 'title', 'description']);
});

it('lets admins filter submissions and update internal review status and notes', function () {
    $customer = User::factory()->customer()->create();
    $admin = User::factory()->admin()->create();
    $submission = FeedbackSubmission::create([
        'user_id' => $customer->id,
        'category' => 'feature_request',
        'title' => 'Save favorite barber',
        'description' => 'Let customers save a preferred barber.',
        'status' => 'new',
    ]);
    FeedbackSubmission::create([
        'user_id' => $customer->id,
        'category' => 'improvement',
        'title' => 'Improve search',
        'description' => 'Make service search easier.',
        'status' => 'new',
    ]);

    $this->actingAs($admin)
        ->getJson('/api/v1/admin/feedback?category=feature_request&status=new')
        ->assertOk()
        ->assertJsonPath('data.0.id', $submission->id)
        ->assertJsonPath('data.0.user.email', $customer->email)
        ->assertJsonPath('meta.total', 1);

    $this->patchJson("/api/v1/admin/feedback/{$submission->id}", [
        'status' => 'planned',
        'admin_note' => 'Include in the next booking update.',
    ])->assertOk()
        ->assertJsonPath('data.status', 'planned')
        ->assertJsonPath('data.admin_note', 'Include in the next booking update.');

    $this->assertDatabaseHas('feedback_submissions', [
        'id' => $submission->id,
        'status' => 'planned',
        'admin_note' => 'Include in the next booking update.',
    ]);
});

it('does not expose the admin feedback inbox to unauthenticated users', function () {
    $this->getJson('/api/v1/admin/feedback')->assertUnauthorized();
});
