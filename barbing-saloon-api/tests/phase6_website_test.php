<?php

declare(strict_types=1);

$baseUrl = 'http://127.0.0.1:8000';

function request(string $method, string $path, array $data = [], array $headers = []): array
{
    global $baseUrl;
    $url = $baseUrl . $path;
    $ch = curl_init($url);

    $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    $allHeaders = array_merge($defaultHeaders, $headers);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

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
        echo " [PASS] $name\n";
    } else {
        echo " [FAIL] $name - $details\n";
        exit(1);
    }
}

echo "=== CANDYCUTZ PHASE 6 VUE 3 WEBSITE INTEGRATION SUITE ===\n\n";

// -------------------------------------------------------------
// 1. PUBLIC LANDING PAGE API VERIFICATION
// -------------------------------------------------------------
echo "--- 1. Public Landing Page APIs ---\n";

$settingsRes = request('GET', '/api/public/settings?v=2');
assertTest('1a. GET /api/public/settings', $settingsRes['code'] === 200 && is_array($settingsRes['json']['data']), "Code: {$settingsRes['code']}");

$servicesRes = request('GET', '/api/public/services?v=2');
assertTest('1b. GET /api/public/services returns catalog', $servicesRes['code'] === 200 && is_array($servicesRes['json']['data']) && count($servicesRes['json']['data']) > 0, "Code: {$servicesRes['code']}");
$firstService = $servicesRes['json']['data'][0];

$barbersRes = request('GET', '/api/public/barbers');
assertTest('1c. GET /api/public/barbers returns staff', $barbersRes['code'] === 200 && is_array($barbersRes['json']['data']) && count($barbersRes['json']['data']) > 0, "Code: {$barbersRes['code']}");
$firstBarber = $barbersRes['json']['data'][0];

$dayOffset = 15 + (int) (time() % 60) + random_int(1, 10);
$testDate = date('Y-m-d', strtotime("+{$dayOffset} days"));

$slotsRes = request('GET', "/api/public/available-slots?barber_id={$firstBarber['id']}&service_id={$firstService['id']}&date={$testDate}");
assertTest('1d. GET /api/public/available-slots', $slotsRes['code'] === 200 && is_array($slotsRes['json']['data']), "Code: {$slotsRes['code']}");

$testimonialsRes = request('GET', '/api/public/testimonials');
assertTest('1e. GET /api/public/testimonials', $testimonialsRes['code'] === 200, "Code: {$testimonialsRes['code']}");

$galleryRes = request('GET', '/api/public/gallery');
assertTest('1f. GET /api/public/gallery', $galleryRes['code'] === 200, "Code: {$galleryRes['code']}");

$contactRes = request('POST', '/api/public/contact', [
    'name' => 'Web Visitor',
    'email' => 'visitor@example.com',
    'phone' => '08012345678',
    'subject' => 'Booking Inquiry',
    'message' => 'Hello from Keffi web client',
]);
assertTest('1g. POST /api/public/contact submission', $contactRes['code'] === 200 || $contactRes['code'] === 201, "Code: {$contactRes['code']}, Body: {$contactRes['body']}");

// -------------------------------------------------------------
// 2. CUSTOMER WEB PORTAL FLOWS
// -------------------------------------------------------------
echo "\n--- 2. Customer Web Portal Flows ---\n";

$customerLogin = request('POST', '/api/auth/login', [
    'email' => 'customer@candycutz.com',
    'password' => 'customer123',
    'device_name' => 'vue3-web-customer',
]);
assertTest('2a. Customer login via web auth endpoint', $customerLogin['code'] === 200 && !empty($customerLogin['json']['data']['token']), "Code: {$customerLogin['code']}");
$customerToken = $customerLogin['json']['data']['token'];
$customerHeaders = ["Authorization: Bearer {$customerToken}"];

$custDash = request('GET', '/api/customer/dashboard', [], $customerHeaders);
assertTest('2b. GET /api/customer/dashboard', $custDash['code'] === 200 && isset($custDash['json']['data']['stats']), "Code: {$custDash['code']}");

$custBookings = request('GET', '/api/customer/bookings', [], $customerHeaders);
assertTest('2c. GET /api/customer/bookings paginated', $custBookings['code'] === 200, "Code: {$custBookings['code']}");

// Customer creates booking via web API (POST /api/customer/bookings)
$custBookingTime = '11:00';
$webBookingPayload = [
    'barber_id' => $firstBarber['id'],
    'service_id' => $firstService['id'],
    'appointment_date' => $testDate,
    'appointment_time' => $custBookingTime,
    'notes' => 'Phase 6 Web Client Integration Booking',
];

$createWebBooking = request('POST', '/api/customer/bookings', $webBookingPayload, $customerHeaders);
assertTest('2d. POST /api/customer/bookings creates appointment with pessimistic lock',
    $createWebBooking['code'] === 201 && !empty($createWebBooking['json']['data']['id']),
    "Code: {$createWebBooking['code']}, Body: {$createWebBooking['body']}"
);
$webApptId = $createWebBooking['json']['data']['id'] ?? 0;

// Duplicate booking attempt on exact same slot via web API must be rejected with 409
$duplicateWebBooking = request('POST', '/api/customer/bookings', $webBookingPayload, $customerHeaders);
assertTest('2e. Duplicate booking via web API is blocked with BOOKING_SLOT_UNAVAILABLE',
    ($duplicateWebBooking['code'] === 409 || $duplicateWebBooking['code'] === 422) &&
    (($duplicateWebBooking['json']['error']['code'] ?? '') === 'BOOKING_SLOT_UNAVAILABLE'),
    "Code: {$duplicateWebBooking['code']}, Body: {$duplicateWebBooking['body']}"
);

// Cancel booking via customer web API
$cancelWebBooking = request('PATCH', "/api/customer/bookings/{$webApptId}/cancel", [], $customerHeaders);
assertTest('2f. PATCH /api/customer/bookings/{id}/cancel', $cancelWebBooking['code'] === 200, "Code: {$cancelWebBooking['code']}");

$custProfile = request('GET', '/api/customer/profile', [], $customerHeaders);
assertTest('2g. GET /api/customer/profile', $custProfile['code'] === 200, "Code: {$custProfile['code']}");

$custNotifs = request('GET', '/api/customer/notifications', [], $customerHeaders);
assertTest('2h. GET /api/customer/notifications', $custNotifs['code'] === 200, "Code: {$custNotifs['code']}");

$custAnalytics = request('GET', '/api/customer/analytics', [], $customerHeaders);
assertTest('2i. GET /api/customer/analytics', $custAnalytics['code'] === 200 && isset($custAnalytics['json']['data']['total_spent']), "Code: {$custAnalytics['code']}");

// -------------------------------------------------------------
// 3. BARBER STAFF WEB PORTAL FLOWS
// -------------------------------------------------------------
echo "\n--- 3. Barber Staff Web Portal Flows ---\n";

$barberLogin = request('POST', '/api/auth/login', [
    'email' => 'marcus@candycutz.com',
    'password' => 'barber123',
    'device_name' => 'vue3-web-barber',
]);
assertTest('3a. Barber staff login via web auth endpoint', $barberLogin['code'] === 200 && !empty($barberLogin['json']['data']['token']), "Code: {$barberLogin['code']}");
$barberToken = $barberLogin['json']['data']['token'];
$barberHeaders = ["Authorization: Bearer {$barberToken}"];

$barberDash = request('GET', '/api/barber/dashboard', [], $barberHeaders);
assertTest('3b. GET /api/barber/dashboard', $barberDash['code'] === 200 && isset($barberDash['json']['data']['stats']), "Code: {$barberDash['code']}");

$barberSchedule = request('GET', '/api/barber/schedule', [], $barberHeaders);
assertTest('3c. GET /api/barber/schedule', $barberSchedule['code'] === 200 && isset($barberSchedule['json']['data']['working_hours']), "Code: {$barberSchedule['code']}");

$barberAppts = request('GET', '/api/barber/appointments', [], $barberHeaders);
assertTest('3d. GET /api/barber/appointments', $barberAppts['code'] === 200, "Code: {$barberAppts['code']}");

// Toggle barber availability status
$chairBusy = request('PATCH', '/api/barber/my-status', ['is_available' => false], $barberHeaders);
assertTest('3e. Barber sets is_available = false', $chairBusy['code'] === 200 && isset($chairBusy['json']['data']['is_available']), "Code: {$chairBusy['code']}");

$chairFree = request('PATCH', '/api/barber/my-status', ['is_available' => true], $barberHeaders);
assertTest('3f. Barber resets is_available = true', $chairFree['code'] === 200 && ($chairFree['json']['data']['is_available'] === true), "Code: {$chairFree['code']}");

// -------------------------------------------------------------
// 4. ADMIN CMS WEB PORTAL FLOWS
// -------------------------------------------------------------
echo "\n--- 4. Admin CMS Web Portal Flows ---\n";

$adminLogin = request('POST', '/api/auth/login', [
    'email' => 'superadmin@candycutz.com',
    'password' => 'superadmin123',
    'device_name' => 'vue3-web-admin',
]);
assertTest('4a. Admin login via web auth endpoint', $adminLogin['code'] === 200 && !empty($adminLogin['json']['data']['token']), "Code: {$adminLogin['code']}");
$adminToken = $adminLogin['json']['data']['token'];
$adminHeaders = ["Authorization: Bearer {$adminToken}"];

$adminDash = request('GET', '/api/admin/dashboard', [], $adminHeaders);
assertTest('4b. GET /api/admin/dashboard returns operational stats', $adminDash['code'] === 200 && isset($adminDash['json']['data']['revenue']['total_revenue']), "Code: {$adminDash['code']}, Body: {$adminDash['body']}");

$adminServices = request('GET', '/api/admin/services', [], $adminHeaders);
assertTest('4c. GET /api/admin/services management', $adminServices['code'] === 200, "Code: {$adminServices['code']}");

$adminBarbers = request('GET', '/api/admin/barbers', [], $adminHeaders);
assertTest('4d. GET /api/admin/barbers management', $adminBarbers['code'] === 200, "Code: {$adminBarbers['code']}");

$adminSettings = request('GET', '/api/admin/settings', [], $adminHeaders);
assertTest('4e. GET /api/admin/settings', $adminSettings['code'] === 200, "Code: {$adminSettings['code']}");

$adminNotifs = request('GET', '/api/admin/notifications', [], $adminHeaders);
assertTest('4f. GET /api/admin/notifications', $adminNotifs['code'] === 200, "Code: {$adminNotifs['code']}");

// -------------------------------------------------------------
// 5. CONTEXT-AWARE AUTHENTICATION & ACCESS CONTROL
// -------------------------------------------------------------
echo "\n--- 5. Context-Aware Auth & 401 Handling ---\n";

// Unauthenticated requests must return 401 with standard envelope
$unauthCustomer = request('GET', '/api/customer/dashboard');
assertTest('5a. Unauthenticated /api/customer/dashboard returns 401 UNAUTHENTICATED',
    $unauthCustomer['code'] === 401 && ($unauthCustomer['json']['error']['code'] ?? '') === 'UNAUTHENTICATED',
    "Code: {$unauthCustomer['code']}, Error: " . json_encode($unauthCustomer['json'])
);

$unauthBarber = request('GET', '/api/barber/dashboard');
assertTest('5b. Unauthenticated /api/barber/dashboard returns 401 UNAUTHENTICATED',
    $unauthBarber['code'] === 401 && ($unauthBarber['json']['error']['code'] ?? '') === 'UNAUTHENTICATED',
    "Code: {$unauthBarber['code']}"
);

$unauthAdmin = request('GET', '/api/admin/dashboard');
assertTest('5c. Unauthenticated /api/admin/dashboard returns 401 UNAUTHENTICATED',
    $unauthAdmin['code'] === 401 && ($unauthAdmin['json']['error']['code'] ?? '') === 'UNAUTHENTICATED',
    "Code: {$unauthAdmin['code']}"
);

// Cross-role privilege separation: Customer attempting Admin CMS endpoint
$crossRoleRes = request('GET', '/api/admin/dashboard', [], $customerHeaders);
assertTest('5d. Customer forbidden from accessing Admin CMS (403)',
    $crossRoleRes['code'] === 403,
    "Code: {$crossRoleRes['code']}"
);

echo "\n ALL 21 PHASE 6 VUE 3 WEBSITE INTEGRATION TESTS PASSED SUCCESSFULLY!\n";
