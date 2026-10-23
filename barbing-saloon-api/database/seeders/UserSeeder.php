<?php

namespace Database\Seeders;

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
                'role' => 'super_admin',
                'phone' => '08030000001',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        // ── Admin Account ──
        $this->updateOrCreateUser(
            ['email' => 'admin@candycutz.com'],
            [
                'name' => 'Admin Manager',
                'real_name' => 'Admin Manager',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '08030000002',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        // ── Barber Demo Staff (O.B.O) ──
        $this->updateOrCreateUser(
            ['email' => 'obo@candycutz.com'],
            [
                'name' => 'O.B.O',
                'real_name' => 'O.B.O',
                'username' => 'obo',
                'password' => Hash::make('barber123'),
                'role' => 'barber',
                'phone' => '08030000003',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        // ── Customer Demo Account ──
        $this->updateOrCreateUser(
            ['email' => 'jay@candycutz.com'],
            [
                'name' => 'Jay Customer',
                'real_name' => 'Jay Customer',
                'username' => 'jay',
                'password' => Hash::make('customer123'),
                'role' => 'customer',
                'phone' => '08031234567',
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

        $user = User::firstOrNew($identity);
        $user->fill($attributes);
        $user->role = $role;
        $user->status = $status;
        $user->is_active = $isActive;
        $user->save();

        return $user;
    }
}
