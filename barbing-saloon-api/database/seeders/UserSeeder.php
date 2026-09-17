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
        User::updateOrCreate(
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
        User::updateOrCreate(
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
        User::updateOrCreate(
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
        User::updateOrCreate(
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
}