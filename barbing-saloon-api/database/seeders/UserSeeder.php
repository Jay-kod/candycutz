<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'superadmin@salon.com'], ['name' => 'Super Admin', 'password' => Hash::make('password'), 'role' => 'super_admin', 'phone' => '08030000001']);
        User::updateOrCreate(['email' => 'admin@salon.com'], ['name' => 'Admin', 'password' => Hash::make('password'), 'role' => 'admin', 'phone' => '08030000002']);
        foreach (range(1, 3) as $i) {
            User::updateOrCreate(['email' => "barber{$i}@salon.com"], ['name' => "Barber {$i}", 'password' => Hash::make('password'), 'role' => 'barber', 'phone' => '0803000000' . ($i + 2)]);
        }
        $customers = [
            ['name' => 'Chinedu Okafor', 'email' => 'chinedu@example.com', 'phone' => '08031234567'],
            ['name' => 'Aisha Bello', 'email' => 'aisha@example.com', 'phone' => '08039876543'],
            ['name' => 'Kelechi Nwosu', 'email' => 'kelechi@example.com', 'phone' => '08035554444'],
            ['name' => 'Bola Adeyemi', 'email' => 'bola@example.com', 'phone' => '08036667777'],
            ['name' => 'Ifunanya Obi', 'email' => 'ifunanya@example.com', 'phone' => '08037778888'],
        ];

        foreach ($customers as $customer) {
            User::updateOrCreate(['email' => $customer['email']], $customer + ['password' => Hash::make('password'), 'role' => 'customer']);
        }
    }
}