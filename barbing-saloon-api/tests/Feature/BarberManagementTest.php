<?php

namespace Tests\Feature;

use App\Domain\Shared\Enums\UserRole;
use App\Models\Barber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarberManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_barber_profile(): void
    {
        $admin = User::factory()->create(['role' => UserRole::admin]);
        $barberUser = User::factory()->create(['name' => 'Original Name', 'email' => 'original@candycutz.com']);
        $barber = Barber::factory()->create([
            'user_id' => $barberUser->id,
            'bio' => 'Original bio',
            'years_experience' => 3,
        ]);

        $response = $this->actingAs($admin)->putJson("/api/v1/admin/barbers/{$barber->id}", [
            'name' => 'Updated Barber Name',
            'bio' => 'Master precision specialist',
            'experience_years' => 7,
            'specialties' => 'Fade, Beard Trim, Hot Towel',
            'status' => 'active',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.name', 'Updated Barber Name');
        $response->assertJsonPath('data.bio', 'Master precision specialist');
        $response->assertJsonPath('data.experience_years', 7);

        $barber->refresh();
        $this->assertEquals('Master precision specialist', $barber->bio);
        $this->assertEquals(7, $barber->years_experience);
        $this->assertEquals('Updated Barber Name', $barber->user->name);
    }

    public function test_customer_cannot_update_barber_profile(): void
    {
        $customer = User::factory()->create(['role' => UserRole::customer]);
        $barber = Barber::factory()->create();

        $response = $this->actingAs($customer)->putJson("/api/v1/admin/barbers/{$barber->id}", [
            'name' => 'Malicious Update',
        ]);

        $response->assertStatus(403);
    }
}
