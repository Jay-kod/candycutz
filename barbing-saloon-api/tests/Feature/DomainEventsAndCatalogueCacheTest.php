<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\Enums\UserRole;
use App\Domain\Shared\Services\CatalogueCache;
use App\Events\PasswordChanged;
use App\Events\ReviewSubmitted;
use App\Events\UserRegistered;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DomainEventsAndCatalogueCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration_dispatches_user_registered_event(): void
    {
        Event::fake([UserRegistered::class]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Event Test Customer',
            'email' => 'event_test@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'phone' => '08012345678',
        ]);

        $response->assertStatus(201);
        Event::assertDispatched(UserRegistered::class, function (UserRegistered $event) {
            return $event->user->email === 'event_test@example.com';
        });
    }

    public function test_password_change_dispatches_password_changed_event(): void
    {
        Event::fake([PasswordChanged::class]);

        $user = User::factory()->create([
            'password' => bcrypt('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/auth/change-password', [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertOk();
        Event::assertDispatched(PasswordChanged::class, function (PasswordChanged $event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    public function test_review_submission_dispatches_review_submitted_event(): void
    {
        Event::fake([ReviewSubmitted::class]);

        $customer = User::factory()->create(['role' => UserRole::customer]);

        $response = $this->actingAs($customer)->postJson('/api/v1/testimonials', [
            'rating' => 5,
            'comment' => 'Exceptional service and friendly atmosphere!',
        ]);

        $response->assertStatus(201);
        Event::assertDispatched(ReviewSubmitted::class, function (ReviewSubmitted $event) {
            return $event->testimonial->rating === 5;
        });
    }

    public function test_catalogue_cache_remembers_and_busts_atomically(): void
    {
        CatalogueCache::bust('services');
        $initialVersion = CatalogueCache::getVersion('services');

        $computedCount = 0;
        $fetcher = function () use (&$computedCount) {
            $computedCount++;

            return ['status' => 'fresh', 'count' => $computedCount];
        };

        // 1. Initial compute & cache
        $first = CatalogueCache::remember('services', 'test_key', $fetcher);
        $this->assertEquals(1, $first['count']);
        $this->assertEquals(1, $computedCount);

        // 2. Second read hits cache (fetcher NOT called)
        $second = CatalogueCache::remember('services', 'test_key', $fetcher);
        $this->assertEquals(1, $second['count']);
        $this->assertEquals(1, $computedCount);

        // 3. Invalidate/bust cache
        CatalogueCache::bust('services');
        $this->assertGreaterThan($initialVersion, CatalogueCache::getVersion('services'));

        // 4. Third read recomputes fresh
        $third = CatalogueCache::remember('services', 'test_key', $fetcher);
        $this->assertEquals(2, $third['count']);
        $this->assertEquals(2, $computedCount);
    }

    public function test_catalogue_endpoints_serve_cached_results_and_bust_on_mutations(): void
    {
        $service = Service::factory()->create([
            'name' => 'Original Haircut',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        // First public request caches
        $res1 = $this->getJson('/api/v1/services');
        $res1->assertOk();
        $this->assertEquals('Original Haircut', $res1->json('data.0.name'));

        // Direct DB update without bust does not change cached output immediately
        $service->update(['name' => 'Updated Haircut Direct DB']);
        $res2 = $this->getJson('/api/v1/services');
        $res2->assertOk();
        $this->assertEquals('Original Haircut', $res2->json('data.0.name'));

        // Atomic bust refreshes cache
        CatalogueCache::bust('services');
        $res3 = $this->getJson('/api/v1/services');
        $res3->assertOk();
        $this->assertEquals('Updated Haircut Direct DB', $res3->json('data.0.name'));
    }
}
