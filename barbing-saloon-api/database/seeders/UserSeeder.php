<?php

namespace Database\Seeders;

use App\Domain\Shared\Enums\UserRole;
use App\Models\Barber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super Admin Demo Accounts ──
        $this->updateOrCreateUser(
            ['email' => 'superadmin@candycutz.com'],
            [
                'name' => 'Super Admin',
                'real_name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('superadmin123'),
                'role' => UserRole::super_admin->value,
                'phone' => '08030000001',
                'is_active' => true,
                'status' => 'active',
            ]
        );

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

        // ── Admin Accounts ──
        $this->updateOrCreateUser(
            ['email' => 'admin@candycutz.com'],
            [
                'name' => 'Admin Manager',
                'real_name' => 'Admin Manager',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => UserRole::admin->value,
                'phone' => '08030000002',
                'is_active' => true,
                'status' => 'active',
            ]
        );

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

        // ── Barber Accounts ──
        $oboUser = $this->updateOrCreateUser(
            ['email' => 'obo@candycutz.com'],
            [
                'name' => 'O.B.O',
                'real_name' => 'O.B.O',
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

        $marcusUser = $this->updateOrCreateUser(
            ['email' => 'marcus@candycutz.com'],
            [
                'name' => 'Marcus Vance',
                'real_name' => 'Marcus Vance',
                'username' => 'marcus',
                'password' => Hash::make('barber123'),
                'role' => UserRole::barber->value,
                'phone' => '08030000002',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        Barber::updateOrCreate(
            ['user_id' => $marcusUser->id],
            [
                'bio' => 'Lead Stylist & Grooming Director. Master of skin fades, beard sculpting, and VIP styling.',
                'specialties' => ['fade', 'line-up', 'beard trim', 'executive cut'],
                'years_experience' => 8,
                'experience_years' => 8,
                'is_available' => true,
                'is_home_service_ready' => true,
                'chair_status' => 'free',
                'rating' => 4.95,
                'total_reviews' => 38,
                'is_featured' => true,
            ]
        );

        // ── Customer Demo Accounts ──
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

        $this->updateOrCreateUser(
            ['email' => 'customer@candycutz.com'],
            [
                'name' => 'Chinedu Customer',
                'real_name' => 'Chinedu Okafor',
                'username' => 'customer',
                'password' => Hash::make('customer123'),
                'role' => UserRole::customer->value,
                'phone' => '08030000003',
                'is_active' => true,
                'status' => 'active',
            ]
        );
    }

    private function updateOrCreateUser(array $identity, array $attributes): User
    {
        $role = $attributes['role'];
        $status = $attributes['status'];
        $isActive = $attributes['is_active'];
        unset($attributes['role'], $attributes['status'], $attributes['is_active']);

        $user = User::withTrashed()->firstOrNew($identity);
        if ($user->trashed()) {
            $user->restore();
        }
        $user->fill($attributes);
        $user->role = $role;
        $user->status = $status;
        $user->is_active = $isActive;
        $user->save();

        return $user;
    }
}
