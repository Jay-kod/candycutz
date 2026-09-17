<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BarberService;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

echo "\n========================================================================\n";
echo " CANDYCUTZ UNIFIED DB & MULTI-CLIENT CONNECTION VERIFICATION TEST\n";
echo "========================================================================\n\n";

$passed = 0;
$failed = 0;

function assertCondition(string $name, bool $condition, string $detail = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$name}" . ($detail ? " ({$detail})" : "") . "\n";
        $passed++;
    } else {
        echo "  [FAIL] {$name}" . ($detail ? " -- {$detail}" : "") . "\n";
        $failed++;
    }
}

// -----------------------------------------------------------------------------
// 1. DIRECT DATABASE USERS INTEGRITY
// -----------------------------------------------------------------------------
echo "[1/6] Direct Database Users Integrity\n";

$totalUsers = User::count();
assertCondition('Total user count in database is exactly 3', $totalUsers === 3, "Found: {$totalUsers}");

$superAdmin = User::where('email', 'superadmin@candycutz.com')->first();
assertCondition('Super Admin account exists', $superAdmin !== null && $superAdmin->role?->value === 'super_admin');
assertCondition('Super Admin password verifies against superadmin123', $superAdmin ? Hash::check('superadmin123', $superAdmin->password) : false);

$barber = User::where('email', 'marcus@candycutz.com')->first();
assertCondition('Barber account exists (Marcus Vance)', $barber !== null && $barber->role?->value === 'barber');
assertCondition('Barber password verifies against barber123', $barber ? Hash::check('barber123', $barber->password) : false);

$customer = User::where('email', 'customer@candycutz.com')->first();
assertCondition('Customer account exists (Chinedu Okafor)', $customer !== null && $customer->role?->value === 'customer');
assertCondition('Customer password verifies against customer123', $customer ? Hash::check('customer123', $customer->password) : false);

// -----------------------------------------------------------------------------
// 2. BARBER PROFILE, SCHEDULE & CATALOG RELATIONS
// -----------------------------------------------------------------------------
echo "\n[2/6] Barber Profile, Schedule & Catalog Relations\n";

$barberProfile = Barber::where('user_id', $barber?->id)->first();
assertCondition('Barber profile linked to Marcus Vance in barbers table', $barberProfile !== null);

$workingHoursCount = WorkingHour::where('barber_id', $barberProfile?->id)->count();
assertCondition('Marcus Vance has working hours configured for all 7 days', $workingHoursCount === 7, "Found: {$workingHoursCount}");

$barberServicesCount = BarberService::where('barber_id', $barberProfile?->id)->count();
assertCondition('Marcus Vance linked to services catalog', $barberServicesCount >= 10, "Found: {$barberServicesCount}");

$appointmentsCount = Appointment::where('barber_id', $barberProfile?->id)->count();
assertCondition('Appointments seeded for customer and barber', $appointmentsCount === 2, "Found: {$appointmentsCount}");

// -----------------------------------------------------------------------------
// 3. BACKEND API HEALTH & DATABASE PING
// -----------------------------------------------------------------------------
echo "\n[3/6] Backend API Health & Database Ping (http://127.0.0.1:8000/api/v1/health)\n";

try {
    $healthResponse = Http::timeout(5)->get('http://127.0.0.1:8000/api/v1/health');
    assertCondition('Backend Health HTTP status is 200 OK', $healthResponse->status() === 200);
    $healthData = $healthResponse->json('data');
    assertCondition('Backend status is healthy', ($healthData['status'] ?? '') === 'healthy');
    assertCondition('Backend database service reports connected', ($healthData['services']['database'] ?? '') === 'connected');
} catch (\Throwable $e) {
    assertCondition('Backend Health Endpoint Accessible', false, $e->getMessage());
}

// -----------------------------------------------------------------------------
// 4. MULTI-ROLE AUTHENTICATION (UNIFIED SANCTUM TOKEN ISSUANCE)
// -----------------------------------------------------------------------------
echo "\n[4/6] Multi-Role Authentication via API (/api/v1/auth/login)\n";

// Customer Login
$customerLogin = Http::timeout(5)->post('http://127.0.0.1:8000/api/v1/auth/login', [
    'identity' => 'customer@candycutz.com',
    'password' => 'customer123',
    'device_name' => 'customer-web-test',
]);
assertCondition('Customer Login with customer@candycutz.com returns 200 OK', $customerLogin->status() === 200);
$customerToken = $customerLogin->json('data.token');
$customerRole = $customerLogin->json('data.user.role');
assertCondition('Customer token issued and role is customer', !empty($customerToken) && $customerRole === 'customer');

// Barber Login
$barberLogin = Http::timeout(5)->post('http://127.0.0.1:8000/api/v1/auth/login', [
    'identity' => 'marcus@candycutz.com',
    'password' => 'barber123',
    'device_name' => 'barber-mobile-test',
]);
assertCondition('Barber Login with marcus@candycutz.com returns 200 OK', $barberLogin->status() === 200);
$barberToken = $barberLogin->json('data.token');
$barberRole = $barberLogin->json('data.user.role');
assertCondition('Barber token issued and role is barber', !empty($barberToken) && $barberRole === 'barber');

// Super Admin Login
$adminLogin = Http::timeout(5)->post('http://127.0.0.1:8000/api/v1/auth/login', [
    'identity' => 'superadmin@candycutz.com',
    'password' => 'superadmin123',
    'device_name' => 'superadmin-web-test',
]);
assertCondition('Super Admin Login with superadmin@candycutz.com returns 200 OK', $adminLogin->status() === 200);
$adminToken = $adminLogin->json('data.token');
$adminRole = $adminLogin->json('data.user.role');
assertCondition('Super Admin token issued and role is super_admin', !empty($adminToken) && $adminRole === 'super_admin');

// Invalid Password Rejection
$invalidLogin = Http::timeout(5)->post('http://127.0.0.1:8000/api/v1/auth/login', [
    'identity' => 'customer@candycutz.com',
    'password' => 'wrongpassword999',
]);
assertCondition('Invalid password rejected (non-200 status)', $invalidLogin->status() !== 200, "Status: {$invalidLogin->status()}");

// -----------------------------------------------------------------------------
// 5. WEB CLIENT PROXY CONNECTION (http://localhost:5174/api/...)
// -----------------------------------------------------------------------------
echo "\n[5/6] Web Client Proxy & DB Communication (http://localhost:5174/api/...)\n";

try {
    $webProxyHealth = Http::timeout(5)->get('http://localhost:5174/api/v1/health');
    assertCondition('Web Client Vite proxy returns 200 for /api/v1/health', $webProxyHealth->status() === 200);
    assertCondition('Web Client Vite proxy verifies DB is connected', $webProxyHealth->json('data.services.database') === 'connected');

    $webProxyBarbers = Http::timeout(5)->get('http://localhost:5174/api/v1/barbers');
    assertCondition('Web Client Vite proxy loads live barbers from MySQL', $webProxyBarbers->status() === 200);
    $barbersList = $webProxyBarbers->json('data') ?? [];
    $barberName = $barbersList[0]['name'] ?? $barbersList[0]['user']['name'] ?? '';
    assertCondition('Marcus Vance is available in Web Client barbers query', count($barbersList) >= 1 && $barberName === 'Marcus Vance', "Name: {$barberName}");
} catch (\Throwable $e) {
    assertCondition('Web Client Proxy Connectivity', false, $e->getMessage());
}

// -----------------------------------------------------------------------------
// 6. MOBILE APP AUTHENTICATED DATA RETRIEVAL (SIMULATION)
// -----------------------------------------------------------------------------
echo "\n[6/6] Mobile App Authenticated Data Retrieval Simulation\n";

// Customer requesting appointments
try {
    $customerAppts = Http::timeout(5)
        ->withToken($customerToken)
        ->get('http://127.0.0.1:8000/api/v1/appointments');
    assertCondition('Customer mobile app client can fetch appointments from MySQL', $customerAppts->status() === 200);
    $customerApptsList = $customerAppts->json('data') ?? [];
    assertCondition('Customer receives seeded booking data', count($customerApptsList) >= 1);
} catch (\Throwable $e) {
    assertCondition('Customer appointments query', false, $e->getMessage());
}

// Barber requesting schedule
try {
    $barberSchedule = Http::timeout(5)
        ->withToken($barberToken)
        ->get('http://127.0.0.1:8000/api/v1/barbers/schedule');
    assertCondition('Barber mobile app client can fetch schedule from MySQL', $barberSchedule->status() === 200);
} catch (\Throwable $e) {
    assertCondition('Barber schedule query', false, $e->getMessage());
}

// -----------------------------------------------------------------------------
// SUMMARY
// -----------------------------------------------------------------------------
echo "\n========================================================================\n";
echo " VERIFICATION SUMMARY: {$passed} PASSED / {$failed} FAILED\n";
echo "========================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
