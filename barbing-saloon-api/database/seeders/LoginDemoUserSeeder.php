<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Shared\Enums\UserRole;
use App\Models\Barber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class LoginDemoUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('Demo login accounts may only be created in local or testing environments.');
        }

        $accounts = [
            [
                'name' => 'Demo Customer',
                'email' => 'kefas@customer.com',
                'role' => UserRole::customer,
                'password' => 'customer123',
            ],
            [
                'name' => 'Demo Administrator',
                'email' => 'webadmin@candycutz.com',
                'role' => UserRole::admin,
                'password' => 'admin123',
            ],
            [
                'name' => 'Demo Barber',
                'email' => 'obo@barber.com',
                'role' => UserRole::barber,
                'password' => 'barber123',
            ],
            [
                'name' => 'Demo Super Admin',
                'email' => 'superadmin@candycutz.com',
                'role' => UserRole::super_admin,
                'password' => 'superadmin123',
            ],
        ];

        $credentials = DB::transaction(function () use ($accounts): array {
            $credentials = [];

            foreach ($accounts as $account) {
                $user = User::withTrashed()->firstOrNew(['email' => $account['email']]);
                if ($user->trashed()) {
                    $user->restore();
                }
                $user->name = $account['name'];
                $user->real_name = $account['name'];
                $user->password = Hash::make($account['password']);
                $user->role = $account['role'];
                $user->is_active = true;
                $user->status = 'active';
                $user->save();
                $user->tokens()->delete();

                if ($account['role'] === UserRole::barber) {
                    Barber::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'bio' => 'Demo barber account for testing the barber portal.',
                            'specialties' => ['fade', 'beard styling'],
                            'years_experience' => 5,
                            'experience_years' => 5,
                            'is_available' => true,
                            'is_home_service_ready' => false,
                            'chair_status' => 'free',
                            'rating' => 5.00,
                            'total_reviews' => 0,
                            'is_featured' => false,
                        ]
                    );
                }

                $credentials[] = [
                    'role' => $account['role']->value,
                    'email' => $account['email'],
                    'password' => $account['password'],
                ];
            }

            return $credentials;
        });

        $this->command->warn('Demo passwords are fixed test credentials for local/demo use.');
        $this->command->table(['Role', 'Email', 'Password'], $credentials);
    }
}
