<?php

declare(strict_types=1);

use App\Domain\Shared\Enums\UserRole;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;

it('requires authentication for barber reviews endpoint', function () {
    $response = $this->getJson('/api/v1/barber/reviews');
    $response->assertStatus(401);
});

it('forbids users without a barber profile from accessing barber reviews', function () {
    $customer = User::factory()->create(['role' => UserRole::customer]);

    $response = $this->actingAs($customer)->getJson('/api/v1/barber/reviews');
    $response->assertStatus(403);
});

it('allows a barber to fetch reviews made to their services and to them', function () {
    $barberUser = User::factory()->create(['role' => UserRole::barber]);
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);

    $otherBarberUser = User::factory()->create(['role' => UserRole::barber]);
    $otherBarber = Barber::factory()->create(['user_id' => $otherBarberUser->id]);

    $service = Service::factory()->create(['barber_id' => $barber->id]);
    $otherService = Service::factory()->create(['barber_id' => $otherBarber->id]);

    $customer = User::factory()->create(['role' => UserRole::customer]);

    // Review directly assigned to this barber
    $review1 = Testimonial::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $service->id,
        'rating' => 5,
        'review' => 'Exceptional fade haircut!',
        'is_approved' => true,
    ]);

    // Review assigned to another barber
    Testimonial::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $otherBarber->id,
        'service_id' => $otherService->id,
        'rating' => 4,
        'review' => 'Great haircut by someone else.',
        'is_approved' => true,
    ]);

    $response = $this->actingAs($barberUser)->getJson('/api/v1/barber/reviews');

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.0.id', $review1->id);
    $response->assertJsonPath('data.0.review', 'Exceptional fade haircut!');
    expect(count($response->json('data')))->toBe(1);
});

it('allows a barber to filter reviews by service, rating, and search query', function () {
    $barberUser = User::factory()->create(['role' => UserRole::barber]);
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);

    $serviceA = Service::factory()->create(['barber_id' => $barber->id, 'name' => 'Classic Cut']);
    $serviceB = Service::factory()->create(['barber_id' => $barber->id, 'name' => 'Beard Grooming']);

    $customer = User::factory()->create(['role' => UserRole::customer]);

    Testimonial::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $serviceA->id,
        'rating' => 5,
        'review' => 'Top notch classic cut',
        'is_approved' => true,
    ]);

    Testimonial::factory()->create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'service_id' => $serviceB->id,
        'rating' => 3,
        'review' => 'Average beard trim',
        'is_approved' => false,
    ]);

    // Filter by service A
    $respA = $this->actingAs($barberUser)->getJson('/api/v1/barber/reviews?service_id='.$serviceA->id);
    $respA->assertStatus(200);
    expect(count($respA->json('data')))->toBe(1);
    expect($respA->json('data.0.service_id'))->toBe($serviceA->id);

    // Filter by rating 5
    $resp5 = $this->actingAs($barberUser)->getJson('/api/v1/barber/reviews?rating=5');
    $resp5->assertStatus(200);
    expect(count($resp5->json('data')))->toBe(1);
    expect($resp5->json('data.0.rating'))->toBe(5);

    // Search query
    $respSearch = $this->actingAs($barberUser)->getJson('/api/v1/barber/reviews?search=classic');
    $respSearch->assertStatus(200);
    expect(count($respSearch->json('data')))->toBe(1);
});

