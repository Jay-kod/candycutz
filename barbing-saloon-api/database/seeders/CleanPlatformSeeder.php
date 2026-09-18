<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Shared\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BarberService;
use App\Models\Branch;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CleanPlatformSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Initiating safe, surgical database cleanup and seed...');

        // 1. Temporarily disable foreign key constraints for clean truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. Truncate transaction, token, and communication tables
        DB::table('personal_access_tokens')->truncate();
        DB::table('device_tokens')->truncate();
        DB::table('notifications')->truncate();
        DB::table('audit_logs')->truncate();
        DB::table('testimonials')->truncate();
        DB::table('payment_transactions')->truncate();
        DB::table('payments')->truncate();
        DB::table('appointment_status_history')->truncate();
        DB::table('appointment_items')->truncate();
        DB::table('appointments')->truncate();
        DB::table('blocked_periods')->truncate();
        DB::table('barber_services')->truncate();
        DB::table('working_hours')->truncate();
        DB::table('barbers')->truncate();
        DB::table('users')->truncate();

        // 3. Create the 3 authoritative accounts with clean IDs (1, 2, 3)
        $superAdmin = User::create([
            'id' => 1,
            'name' => 'Super Admin',
            'real_name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@candycutz.com',
            'password' => Hash::make('superadmin123'),
            'role' => UserRole::super_admin,
            'phone' => '08030000001',
            'is_active' => true,
            'status' => 'active',
        ]);

        $barberUser = User::create([
            'id' => 2,
            'name' => 'Marcus Vance',
            'real_name' => 'Marcus Vance',
            'username' => 'marcus',
            'email' => 'marcus@candycutz.com',
            'password' => Hash::make('barber123'),
            'role' => UserRole::barber,
            'phone' => '08030000002',
            'is_active' => true,
            'status' => 'active',
        ]);

        $customer = User::create([
            'id' => 3,
            'name' => 'Chinedu Okafor',
            'real_name' => 'Chinedu Okafor',
            'username' => 'customer',
            'email' => 'customer@candycutz.com',
            'password' => Hash::make('customer123'),
            'role' => UserRole::customer,
            'phone' => '08030000003',
            'is_active' => true,
            'status' => 'active',
        ]);

        $this->command->info('Created exactly 3 core users: Super Admin (1), Barber (2), Customer (3).');

        // 4. Resolve flagship branch
        $branch = Branch::where('slug', 'keffi-central')->first() ?? Branch::first();
        $branchId = $branch ? $branch->id : null;

        // 5. Create Marcus Vance's Barber Profile
        $barberProfile = Barber::create([
            'id' => 1,
            'user_id' => $barberUser->id,
            'branch_id' => $branchId,
            'bio' => 'Lead Stylist & Grooming Director at CandyCutz Keffi Flagship. Master of skin fades, beard sculpting, and VIP styling.',
            'specialties' => ['fade', 'line-up', 'beard trim', 'executive cut', 'combo'],
            'years_experience' => 8,
            'experience_years' => 8,
            'instagram_url' => 'https://instagram.com/candycutz_marcus',
            'display_order' => 1,
            'is_featured' => true,
            'is_available' => true,
            'is_home_service_ready' => true,
            'rating' => 4.95,
            'total_reviews' => 24,
            'chair_status' => 'free',
        ]);

        $this->command->info('Created Barber profile for Marcus Vance (Barber ID: 1).');

        // 6. Seed working hours for Marcus Vance for all 7 days of the week
        // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
        for ($day = 0; $day <= 6; $day++) {
            WorkingHour::create([
                'barber_id' => $barberProfile->id,
                'day_of_week' => $day,
                'open_time' => ($day === 0) ? '10:00:00' : '08:00:00',
                'close_time' => ($day === 0) ? '18:00:00' : '20:00:00',
                'is_closed' => false,
            ]);
        }
        $this->command->info('Configured working hours (7 days, open) for Marcus Vance.');

        // 7. Associate all active services with Marcus Vance
        $services = Service::all();
        foreach ($services as $service) {
            BarberService::create([
                'barber_id' => $barberProfile->id,
                'service_id' => $service->id,
                'is_offered' => true,
            ]);
        }
        $this->command->info("Linked {$services->count()} services to Marcus Vance.");

        // 8. Seed 2 clean appointments between Chinedu Okafor and Marcus Vance
        $service1 = Service::find(2) ?? $services->first(); // Premium Fade (4000 NGN)
        $service2 = Service::find(5) ?? $services->skip(1)->first() ?? $service1; // Combo Cut (5000 NGN)

        // Upcoming Confirmed Appointment (Tomorrow at 10:00 AM)
        $tomorrow = Carbon::now()->addDay()->toDateString();
        Appointment::create([
            'booking_reference' => 'CC-KEF-'.strtoupper(Str::random(6)),
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'client_name' => $customer->name,
            'client_phone' => $customer->phone,
            'client_email' => $customer->email,
            'barber_id' => $barberProfile->id,
            'service_id' => $service1->id,
            'appointment_type' => 'in_shop',
            'appointment_date' => $tomorrow,
            'appointment_time' => '10:00:00',
            'end_time' => '10:45:00',
            'total_duration_minutes' => 45,
            'total_amount' => $service1->price ?? 4000.00,
            'total_price' => $service1->price ?? 4000.00,
            'deposit_paid' => true,
            'deposit_amount' => 1000.00,
            'status' => 'confirmed',
            'notes' => 'Customer requested precision temple fade with low taper.',
        ]);

        // Past Completed Appointment (3 Days ago at 2:00 PM)
        $pastDate = Carbon::now()->subDays(3)->toDateString();
        Appointment::create([
            'booking_reference' => 'CC-KEF-'.strtoupper(Str::random(6)),
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'client_name' => $customer->name,
            'client_phone' => $customer->phone,
            'client_email' => $customer->email,
            'barber_id' => $barberProfile->id,
            'service_id' => $service2->id,
            'appointment_type' => 'in_shop',
            'appointment_date' => $pastDate,
            'appointment_time' => '14:00:00',
            'end_time' => '15:00:00',
            'total_duration_minutes' => 60,
            'total_amount' => $service2->price ?? 5000.00,
            'total_price' => $service2->price ?? 5000.00,
            'deposit_paid' => true,
            'deposit_amount' => 1000.00,
            'status' => 'completed',
            'notes' => 'VIP appointment completed with gold beard oil application.',
        ]);

        $this->command->info('Seeded 2 realistic sample appointments (1 upcoming, 1 completed).');

        // 9. Re-enable foreign key constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Database cleanup and seed completed successfully!');
    }
}
