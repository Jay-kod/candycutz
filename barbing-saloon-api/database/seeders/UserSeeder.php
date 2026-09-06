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

        User::updateOrCreate(
            ['email' => 'superadmin@salon.com'],
            [
                'name' => 'Super Admin',
                'real_name' => 'Super Admin',
                'username' => 'superadmin_salon',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'phone' => '08030000001',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        // ── Admin Accounts ──
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

        User::updateOrCreate(
            ['email' => 'admin@salon.com'],
            [
                'name' => 'Admin',
                'real_name' => 'Admin',
                'username' => 'admin_salon',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '08030000002',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        // ── Barber Demo Staff (Marcus Vance) ──
        User::updateOrCreate(
            ['email' => 'marcus@candycutz.com'],
            [
                'name' => 'Marcus Vance',
                'real_name' => 'Marcus Vance',
                'username' => 'marcus',
                'password' => Hash::make('barber123'),
                'role' => 'barber',
                'phone' => '08030000003',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        foreach (range(1, 3) as $i) {
            User::updateOrCreate(
                ['email' => "barber{$i}@salon.com"],
                [
                    'name' => "Barber {$i}",
                    'real_name' => "Barber {$i}",
                    'username' => "barber{$i}",
                    'password' => Hash::make('password'),
                    'role' => 'barber',
                    'phone' => '0803000000' . ($i + 2),
                    'is_active' => true,
                    'status' => 'active',
                ]
            );
        }

        // ── Customer Demo Account ──
        User::updateOrCreate(
            ['email' => 'customer@candycutz.com'],
            [
                'name' => 'Chinedu Okafor',
                'real_name' => 'Chinedu Okafor',
                'username' => 'customer',
                'password' => Hash::make('customer123'),
                'role' => 'customer',
                'phone' => '08031234567',
                'is_active' => true,
                'status' => 'active',
            ]
        );

        $customers = [
            ['name' => 'Chinedu Okafor', 'email' => 'chinedu@example.com', 'phone' => '08031234567', 'username' => 'chinedu'],
            ['name' => 'Aisha Bello', 'email' => 'aisha@example.com', 'phone' => '08039876543', 'username' => 'aisha'],
            ['name' => 'Kelechi Nwosu', 'email' => 'kelechi@example.com', 'phone' => '08035554444', 'username' => 'kelechi'],
            ['name' => 'Bola Adeyemi', 'email' => 'bola@example.com', 'phone' => '08036667777', 'username' => 'bola'],
            ['name' => 'Ifunanya Obi', 'email' => 'ifunanya@example.com', 'phone' => '08037778888', 'username' => 'ifunanya'],
        ];

        foreach ($customers as $customer) {
            User::updateOrCreate(
                ['email' => $customer['email']],
                $customer + [
                    'password' => Hash::make('password'),
                    'role' => 'customer',
                    'is_active' => true,
                    'status' => 'active',
                ]
            );
        }
    }
}