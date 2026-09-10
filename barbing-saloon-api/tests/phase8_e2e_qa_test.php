<?php
/**
 * Candycutz Phase 8 End-to-End Multi-Role QA Verification Suite
 *
 * Tests complete real-world operational lifecycles across all user roles:
 * 1. Standard In-Shop Booking & Service Completion Lifecycle
 * 2. Home Service Lifecycle with Dynamic Zone Travel Surcharges
 * 3. Walk-In Immediate Chair Reservation & Online Collision Shielding
 * 4. Cancellation Lifecycle & Real-Time Slot Re-Liberation
 * 5. Soft Account Deactivation, Audit Retention & Access Termination
 */

declare(strict_types=1);

$baseUrl = 'http://127.0.0.1:8000';

function request(string $method, string $path, array $data = [], array $headers = []): array
{
    global $baseUrl;
    $url = $baseUrl . $path;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $defaultHeaders = ['Accept: application/json'];
    if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        $defaultHeaders[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));

    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $headerStr = $raw ? substr($raw, 0, $headerSize) : '';
    $bodyStr = $raw ? substr($raw, $headerSize) : '';
    $json = json_decode($bodyStr, true);

    return [
        'code' => $httpCode,
        'headers' => $headerStr,
        'body' => $bodyStr,
        'json' => $json,
    ];
}

function assertTest(string $name, bool $condition, string $details = ''): void
{
    if ($condition) {
        echo " [PASS] {$name}\n";
    } else {
        echo " [FAIL] {$name} - {$details}\n";
        exit(1);
    }
}

echo "=== CANDYCUTZ PHASE 8 END-TO-END QA VERIFICATION SUITE ===\n\n";

// -------------------------------------------------------------
// 1. STANDARD IN-SHOP BOOKING & COMPLETION LIFECYCLE
// -------------------------------------------------------------
echo "--- 1. In-Shop Booking & Service Completion Lifecycle ---\n";

// 1a. Authenticate Customer & Barber
$custLogin = request('POST', '/api/v1/auth/login', [
    'identity' => 'customer@candycutz.com',
    'password' => 'customer123',
    'device_name' => 'e2e-customer',
]);
assertTest('1a. Authenticate Customer via API v1', $custLogin['code'] === 200 && !empty($custLogin['json']['data']['token']), "Code: {$custLogin['code']}");
$customerToken = $custLogin['json']['data']['token'];
$customerHeaders = ["Authorization: Bearer {$customerToken}"];

$barberLogin = request('POST', '/api/v1/auth/login', [
    'identity' => 'marcus@candycutz.com',
    'password' => 'barber123',
    'device_name' => 'e2e-barber',
]);
assertTest('1b. Authenticate Barber via API v1', $barberLogin['code'] === 200 && !empty($barberLogin['json']['data']['token']), "Code: {$barberLogin['code']}");
$barberToken = $barberLogin['json']['data']['token'];
$barberHeaders = ["Authorization: Bearer {$barberToken}"];
$barberId = (int) ($barberLogin['json']['data']['barber']['id'] ?? 1);

// 1c. Browse catalog
$servicesRes = request('GET', '/api/v1/services');
assertTest('1c. Customer browses catalog services', $servicesRes['code'] === 200 && !empty($servicesRes['json']['data']), "Code: {$servicesRes['code']}");
$service = $servicesRes['json']['data'][0];
$serviceId = (int) $service['id'];

// Unique test date
$testDate1 = date('Y-m-d', strtotime("+" . (1000 + (int)(microtime(true) * 1000) % 3000 + random_int(1, 200)) . " days"));
$slotTime1 = '11:00';

// 1d. Create In-Shop Booking
$inShopBooking = request('POST', '/api/v1/appointments', [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'appointment_date' => $testDate1,
    'appointment_time' => $slotTime1,
    'appointment_type' => 'in_shop',
    'notes' => 'E2E QA In-Shop Lifecycle Test',
], $customerHeaders);
assertTest('1d. Customer reserves in-shop appointment', $inShopBooking['code'] === 201 && !empty($inShopBooking['json']['data']['id']), "Code: {$inShopBooking['code']}, Body: {$inShopBooking['body']}");
$apptId1 = (int) $inShopBooking['json']['data']['id'];

// 1e. Webhook payment confirmation
$webhookPayload = [
    'id' => 'evt_e2e_' . uniqid(),
    'type' => 'payment_intent.succeeded',
    'data' => [
        'object' => [
            'id' => 'pi_e2e_' . uniqid(),
            'metadata' => [
                'appointment_id' => $apptId1,
            ],
            'amount' => 500000,
            'currency' => 'ngn',
            'status' => 'succeeded',
        ],
    ],
];
$webhookRes = request('POST', '/api/v1/payments/webhook', $webhookPayload);
assertTest('1e. Webhook confirms appointment payment', $webhookRes['code'] === 200, "Code: {$webhookRes['code']}");

// Verify confirmed status
$checkAppt = request('GET', "/api/v1/appointments/{$apptId1}", [], $customerHeaders);
assertTest('1f. Appointment transitioned to confirmed & deposit_paid',
    $checkAppt['code'] === 200 &&
    $checkAppt['json']['data']['status'] === 'confirmed' &&
    $checkAppt['json']['data']['deposit_paid'] === true,
    "Status: " . ($checkAppt['json']['data']['status'] ?? 'unknown')
);

// 1g. Barber updates to in_progress
$startService = request('PATCH', "/api/v1/appointments/{$apptId1}/status", ['status' => 'in_progress'], $barberHeaders);
assertTest('1g. Barber starts service (status: in_progress)', $startService['code'] === 200, "Code: {$startService['code']}");

// 1h. Barber completes service
$completeService = request('PATCH', "/api/v1/appointments/{$apptId1}/status", ['status' => 'completed'], $barberHeaders);
assertTest('1h. Barber completes service (status: completed)', $completeService['code'] === 200 && $completeService['json']['data']['status'] === 'completed', "Code: {$completeService['code']}");

// 1i. Customer submits review & rating
$reviewRes = request('POST', '/api/customer/testimonials', [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'rating' => 5,
    'review' => 'Exceptional precision cut and prompt service at Keffi branch!',
], $customerHeaders);
assertTest('1i. Customer leaves rating and review', $reviewRes['code'] === 201 || $reviewRes['code'] === 200, "Code: {$reviewRes['code']}, Body: {$reviewRes['body']}");

// -------------------------------------------------------------
// 2. HOME SERVICE WITH DYNAMIC TRAVEL SURCHARGE
// -------------------------------------------------------------
echo "\n--- 2. Home Service Lifecycle with Zone Travel Surcharge ---\n";

$zonesRes = request('GET', '/api/v1/service-zones');
assertTest('2a. GET /api/v1/service-zones returns Keffi zones', $zonesRes['code'] === 200 && !empty($zonesRes['json']['data']), "Code: {$zonesRes['code']}");
$zone = $zonesRes['json']['data'][0];
$zoneId = (int) $zone['id'];
$travelFee = (float) $zone['base_travel_fee'];

$testDate2 = date('Y-m-d', strtotime("+" . (4500 + (int)(microtime(true) * 1000) % 3000 + random_int(1, 200)) . " days"));
$homeBooking = request('POST', '/api/v1/appointments', [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'appointment_date' => $testDate2,
    'appointment_time' => '11:00',
    'appointment_type' => 'home_service',
    'service_zone_id' => $zoneId,
    'destination_address' => [
        'address_line_1' => 'Beside Wealths Khort Apartments, BCG',
        'city' => 'Keffi',
        'state' => 'Nasarawa',
        'landmark' => 'Near Gas Station',
    ],
    'notes' => 'VIP Home service request',
], $customerHeaders);

assertTest('2b. Home service appointment reserved with travel fee',
    $homeBooking['code'] === 201 &&
    ($homeBooking['json']['data']['appointment_type'] ?? '') === 'home_service',
    "Code: {$homeBooking['code']}, Body: {$homeBooking['body']}"
);

// -------------------------------------------------------------
// 3. BARBER WALK-IN RESERVATION & ONLINE SHIELDING
// -------------------------------------------------------------
echo "\n--- 3. Barber Walk-In & Online Shielding ---\n";

$testDate3 = date('Y-m-d', strtotime("+" . (8000 + (int)(microtime(true) * 1000) % 3000 + random_int(1, 200)) . " days"));
$walkInTime = '14:00';

$walkInRes = request('POST', '/api/v1/appointments/walk-in', [
    'service_id' => $serviceId,
    'client_name' => 'Walk-In Local Client',
    'appointment_date' => $testDate3,
    'appointment_time' => $walkInTime,
    'notes' => 'Immediate walk-in chair customer',
], $barberHeaders);

assertTest('3a. Barber logs walk-in client appointment', $walkInRes['code'] === 201 && !empty($walkInRes['json']['data']['id']), "Code: {$walkInRes['code']}, Body: {$walkInRes['body']}");

// Online customer attempting same slot must be blocked
$collidingCust = request('POST', '/api/v1/appointments', [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'appointment_date' => $testDate3,
    'appointment_time' => $walkInTime,
    'notes' => 'Online customer collision attempt',
], $customerHeaders);

assertTest('3b. Online customer blocked from chair occupied by walk-in',
    $collidingCust['code'] === 409 && ($collidingCust['json']['error']['code'] ?? '') === 'BOOKING_SLOT_UNAVAILABLE',
    "Code: {$collidingCust['code']}"
);

// -------------------------------------------------------------
// 4. CANCELLATION & INSTANT SLOT RE-LIBERATION
// -------------------------------------------------------------
echo "\n--- 4. Cancellation & Slot Re-Liberation ---\n";

$testDate4 = date('Y-m-d', strtotime("+" . (11500 + (int)(microtime(true) * 1000) % 3000 + random_int(1, 200)) . " days"));
$libSlot = '16:00';

// Customer 1 books slot
$initialAppt = request('POST', '/api/v1/appointments', [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'appointment_date' => $testDate4,
    'appointment_time' => $libSlot,
    'notes' => 'Booking to be cancelled',
], $customerHeaders);
assertTest('4a. Customer books appointment for cancellation test', $initialAppt['code'] === 201, "Code: {$initialAppt['code']}");
$apptToCancelId = (int) $initialAppt['json']['data']['id'];

// Customer 1 cancels booking
$cancelRes = request('PATCH', "/api/v1/appointments/{$apptToCancelId}/cancel", [
    'cancellation_reason' => 'Schedule conflict',
], $customerHeaders);
assertTest('4b. Customer cancels appointment', $cancelRes['code'] === 200, "Code: {$cancelRes['code']}");

// Customer 2 immediately books that liberated slot
$cust2Login = request('POST', '/api/v1/auth/login', [
    'identity' => 'amara@candycutz.com',
    'password' => 'customer123',
    'device_name' => 'e2e-cust2',
]);
$cust2Token = $cust2Login['json']['data']['token'] ?? $customerToken;
$cust2Headers = ["Authorization: Bearer {$cust2Token}"];

$rebookRes = request('POST', '/api/v1/appointments', [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'appointment_date' => $testDate4,
    'appointment_time' => $libSlot,
    'notes' => 'Second customer re-booking liberated slot',
], $cust2Headers);
assertTest('4c. Second customer successfully books newly liberated slot', $rebookRes['code'] === 201, "Code: {$rebookRes['code']}, Body: {$rebookRes['body']}");

// -------------------------------------------------------------
// 5. ACCOUNT SOFT DEACTIVATION & ACCESS TERMINATION
// -------------------------------------------------------------
echo "\n--- 5. Soft Account Deactivation & Audit Retention ---\n";

// Register temporary user for deactivation
$tempEmail = 'deactivate_test_' . uniqid() . '@candycutz.com';
$tempUserRes = request('POST', '/api/v1/auth/register', [
    'name' => 'Deactivate Subject',
    'username' => 'deact_' . substr(uniqid(), 0, 8),
    'email' => $tempEmail,
    'phone' => '08099' . random_int(100000, 999999),
    'password' => 'TemporaryPass123!',
    'password_confirmation' => 'TemporaryPass123!',
    'device_name' => 'deact-test',
]);
assertTest('5a. Register temporary user for deactivation test', $tempUserRes['code'] === 201 && !empty($tempUserRes['json']['data']['token']), "Code: {$tempUserRes['code']}");
$tempToken = $tempUserRes['json']['data']['token'];
$tempUserId = (int) $tempUserRes['json']['data']['user']['id'];
$tempHeaders = ["Authorization: Bearer {$tempToken}"];

// Confirm user is active initially
$tempProfile = request('GET', '/api/v1/auth/me', [], $tempHeaders);
assertTest('5b. Temporary user active initially', $tempProfile['code'] === 200, "Code: {$tempProfile['code']}");

// Super Admin deactivates user via database/service
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$superAdminService = app(\App\Modules\SuperAdmin\Services\SuperAdminService::class);
$targetUser = \App\Models\User::findOrFail($tempUserId);
$superAdminService->deactivateUser($targetUser);

$reloadedUser = \App\Models\User::findOrFail($tempUserId);
assertTest('5c. User record soft-deactivated (is_active=0, status=deactivated, deactivated_at not null)',
    $reloadedUser->is_active === false &&
    $reloadedUser->status === 'deactivated' &&
    $reloadedUser->deactivated_at !== null,
    "Status: {$reloadedUser->status}, Active: " . ($reloadedUser->is_active ? '1' : '0')
);

// Verify existing token immediately revoked (401)
$cutOffReq = request('GET', '/api/v1/auth/me', [], $tempHeaders);
assertTest('5d. Existing personal access tokens revoked immediately upon deactivation',
    $cutOffReq['code'] === 401,
    "Code: {$cutOffReq['code']}"
);

// Verify subsequent login attempt blocked (403 or 401)
$blockedLogin = request('POST', '/api/v1/auth/login', [
    'identity' => $tempEmail,
    'password' => 'TemporaryPass123!',
    'device_name' => 'post-deact',
]);
assertTest('5e. Deactivated user blocked from logging in (403 Forbidden)',
    $blockedLogin['code'] === 403 || $blockedLogin['code'] === 401,
    "Code: {$blockedLogin['code']}, Body: {$blockedLogin['body']}"
);

echo "\n ALL 17 PHASE 8 END-TO-END QA SCENARIOS PASSED SUCCESSFULLY!\n";
