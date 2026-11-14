<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Database\Seeders\WebUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows all demo accounts to login via /api/v1/auth/login', function () {
    app(UserSeeder::class)->run();
    app(WebUserSeeder::class)->run();

    $accounts = [
        ['email' => 'kefas@customer.com', 'password' => 'customer123', 'role' => 'customer'],
        ['email' => 'customer@candycutz.com', 'password' => 'customer123', 'role' => 'customer'],
        ['email' => 'jay@candycutz.com', 'password' => 'customer123', 'role' => 'customer'],
        ['email' => 'mark@barber.com', 'password' => 'barber123', 'role' => 'barber'],
        ['email' => 'marcus@candycutz.com', 'password' => 'barber123', 'role' => 'barber'],
        ['email' => 'obo@candycutz.com', 'password' => 'barber123', 'role' => 'barber'],
        ['email' => 'webadmin@candycutz.com', 'password' => 'admin123', 'role' => 'admin'],
        ['email' => 'admin@candycutz.com', 'password' => 'admin123', 'role' => 'admin'],
        ['email' => 'websuperadmin@candycutz.com', 'password' => 'superadmin123', 'role' => 'super_admin'],
        ['email' => 'superadmin@candycutz.com', 'password' => 'superadmin123', 'role' => 'super_admin'],
    ];

    foreach ($accounts as $acc) {
        $res = $this->postJson('/api/v1/auth/login', [
            'email' => $acc['email'],
            'password' => $acc['password'],
        ]);

        if ($res->status() !== 200) {
            dump("FAILED for {$acc['email']}:", $res->json());
        }

        $res->assertStatus(200);
        $role = $res->json('data.user.role');
        if (is_array($role)) {
            $role = $role['value'] ?? $role['name'] ?? null;
        }
        expect($role)->toBe($acc['role']);
    }
});
