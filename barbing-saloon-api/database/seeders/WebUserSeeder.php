<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Shared\Enums\UserRole;
use App\Models\Barber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WebUserSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Web Accounts ──────────────────────────────────────

        // Customer: kefas@customer.com / customer123
        $this->updateOrCreateUser(
            ['email' => 'kefas@customer.com'],
            [
                'name' => 'Kefas Customer',
                'real_name' => 'Kefas Customer',
                'username' => 'kefas',
                'password' => Hash::make('customer123'),
                'role' => UserRole::customer->value,
                'phone' => '08031112222',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        // Barber: mark@barber.com / barber123 (with linked active Barber profile)
        $markUser = $this->updateOrCreateUser(
            ['email' => 'mark@barber.com'],
            [
                'name' => 'Mark Barber',
                'real_name' => 'Mark Barber',
                'username' => 'mark',
                'password' => Hash::make('barber123'),
                'role' => UserRole::barber->value,
                'phone' => '08032223333',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        Barber::updateOrCreate(
            ['user_id' => $markUser->id],
            [
                'bio' => 'Master barber specializing in precision fades, executive beard styling, and modern grooming.',
                'specialties' => ['fade', 'beard', 'line-up', 'scissor cut'],
                'years_experience' => 7,
                'experience_years' => 7,
                'is_available' => true,
                'is_home_service_ready' => true,
                'chair_status' => 'free',
                'rating' => 5.00,
                'total_reviews' => 24,
                'is_featured' => true,
            ]
        );

        // Admin: webadmin@candycutz.com / admin123
        $this->updateOrCreateUser(
            ['email' => 'webadmin@candycutz.com'],
            [
                'name' => 'Web Administrator',
                'real_name' => 'Web Administrator',
                'username' => 'webadmin',
                'password' => Hash::make('admin123'),
                'role' => UserRole::admin->value,
                'phone' => '08033334444',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        // Super Admin: websuperadmin@candycutz.com / superadmin123
        $this->updateOrCreateUser(
            ['email' => 'websuperadmin@candycutz.com'],
            [
                'name' => 'Web Super Admin',
                'real_name' => 'Web Super Admin',
                'username' => 'websuperadmin',
                'password' => Hash::make('superadmin123'),
                'role' => UserRole::super_admin->value,
                'phone' => '08034445555',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        // ── 2. Preserved Mobile App Accounts ──────────────────────

        // Customer: jay@candycutz.com / customer123
        $this->updateOrCreateUser(
            ['email' => 'jay@candycutz.com'],
            [
                'name' => 'Jay Customer',
                'real_name' => 'Jay Customer',
                'username' => 'jay',
                'password' => Hash::make('customer123'),
                'role' => UserRole::customer->value,
                'phone' => '08031234567',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        // Barber: obo@candycutz.com / barber123
        $oboUser = $this->updateOrCreateUser(
            ['email' => 'obo@candycutz.com'],
            [
                'name' => 'O.B.O Barber',
                'real_name' => 'O.B.O Barber',
                'username' => 'obo',
                'password' => Hash::make('barber123'),
                'role' => UserRole::barber->value,
                'phone' => '08030000003',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        Barber::updateOrCreate(
            ['user_id' => $oboUser->id],
            [
                'bio' => 'Top-tier stylist and resident barber known for iconic VIP cuts and razor precision.',
                'specialties' => ['vip cut', 'fade', 'beard grooming'],
                'years_experience' => 8,
                'experience_years' => 8,
                'is_available' => true,
                'is_home_service_ready' => true,
                'chair_status' => 'free',
                'rating' => 4.90,
                'total_reviews' => 42,
                'is_featured' => true,
            ]
        );

        // ── 3. Designated Mobile Demo Accounts ────────────────────

        // Customer: fonetestcuz@candycutz.com / customer123
        $this->updateOrCreateUser(
            ['email' => 'fonetestcuz@candycutz.com'],
            [
                'name' => 'Demo Phone Customer',
                'real_name' => 'Demo Phone Customer',
                'username' => 'fonetestcuz',
                'password' => Hash::make('customer123'),
                'role' => UserRole::customer->value,
                'phone' => '08091112233',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        // Barber: fonetestbar@candycutz.com / barber123
        $phoneBarber = $this->updateOrCreateUser(
            ['email' => 'fonetestbar@candycutz.com'],
            [
                'name' => 'Demo Phone Barber',
                'real_name' => 'Demo Phone Barber',
                'username' => 'fonetestbar',
                'password' => Hash::make('barber123'),
                'role' => UserRole::barber->value,
                'phone' => '08094445566',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        Barber::updateOrCreate(
            ['user_id' => $phoneBarber->id],
            [
                'bio' => 'Flagship resident barber for mobile app testing and appointments.',
                'specialties' => ['fade', 'scissor cut', 'beard styling', 'vip grooming'],
                'years_experience' => 6,
                'experience_years' => 6,
                'is_available' => true,
                'is_home_service_ready' => true,
                'chair_status' => 'free',
                'rating' => 5.00,
                'total_reviews' => 18,
                'is_featured' => true,
            ]
        );
    }

    private function updateOrCreateUser(array $identity, array $attributes): User
    {
        $role = $attributes['role'];
        $status = $attributes['status'];
        $isActive = $attributes['is_active'];
        unset($attributes['role'], $attributes['status'], $attributes['is_active']);

        $user = User::firstOrNew($identity);
        $user->fill($attributes);
        $user->role = $role;
        $user->status = $status;
        $user->is_active = $isActive;
        $user->save();

        return $user;
    }
}
