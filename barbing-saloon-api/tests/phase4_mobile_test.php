<?php

declare(strict_types=1);

/**
 * Candycutz Phase 4 Mobile App Convergence Verification Suite
 * Tests authoritative API v1 endpoints supporting the unified mobile client.
 */

$baseUrl = 'http://127.0.0.1:8000';
$timestamp = time();

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

    if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $headerStr = substr($raw, 0, $headerSize);
    $bodyStr = substr($raw, $headerSize);
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

echo "=== CANDYCUTZ PHASE 4 UNIFIED MOBILE APP VERIFICATION SUITE ===\n\n";

// 1. Health & Server check
$res = request('GET', '/api/health');
assertTest('1. API Health Check', $res['code'] === 200, "Code: {$res['code']}");

// 2. Customer Authentication
$customerLogin = request('POST', '/api/v1/auth/login', [
    'identity' => 'customer@candycutz.com',
    'password' => 'customer123',
    'device_name' => 'candycutz-mobile-customer',
]);
assertTest('2. Customer Login via API v1', $customerLogin['code'] === 200 && !empty($customerLogin['json']['data']['token']), "Code: {$customerLogin['code']}");
$customerToken = $customerLogin['json']['data']['token'];
$customerUser = $customerLogin['json']['data']['user'];
assertTest('2b. Customer Role is "customer"', $customerUser['role'] === 'customer', "Got role: " . ($customerUser['role'] ?? 'null'));

// 3. Customer Profile & Wallet
$customerProfile = request('GET', '/api/v1/auth/me', [], ["Authorization: Bearer {$customerToken}"]);
assertTest('3. Customer Profile via API v1', $customerProfile['code'] === 200, "Code: {$customerProfile['code']}");
assertTest('3b. Customer Wallet Balance present', isset($customerProfile['json']['data']['wallet_balance']), "Got: " . json_encode($customerProfile['json']));

// 4. Public Services & Barbers for Mobile Home Screen
$servicesRes = request('GET', '/api/v1/services');
assertTest('4. Fetch Services for Mobile', $servicesRes['code'] === 200 && is_array($servicesRes['json']['data']), "Code: {$servicesRes['code']}");
$barbersRes = request('GET', '/api/v1/barbers');
assertTest('4b. Fetch Barbers for Mobile', $barbersRes['code'] === 200 && is_array($barbersRes['json']['data']), "Code: {$barbersRes['code']}");

// 5. Customer Bookings Endpoint (GET /api/v1/appointments)
$myBookingsRes = request('GET', '/api/v1/appointments', [], ["Authorization: Bearer {$customerToken}"]);
assertTest('5. Customer Appointments List', $myBookingsRes['code'] === 200, "Code: {$myBookingsRes['code']}");

// 6. Barber Staff Authentication
$barberLogin = request('POST', '/api/v1/auth/login', [
    'identity' => 'marcus@candycutz.com',
    'password' => 'barber123',
    'device_name' => 'candycutz-mobile-barber',
]);
assertTest('6. Barber Staff Login via API v1', $barberLogin['code'] === 200 && !empty($barberLogin['json']['data']['token']), "Code: {$barberLogin['code']}");
$barberToken = $barberLogin['json']['data']['token'];
$barberUser = $barberLogin['json']['data']['user'];
assertTest('6b. Barber Role is "barber"', $barberUser['role'] === 'barber', "Got role: " . ($barberUser['role'] ?? 'null'));

// 7. Barber Chair Status Update
$chairRes = request('PATCH', '/api/v1/barbers/chair-status', ['chair_status' => 'busy'], ["Authorization: Bearer {$barberToken}"]);
assertTest('7. Barber Update Chair Status (busy)', in_array($chairRes['code'], [200, 204], true), "Code: {$chairRes['code']}, Body: {$chairRes['body']}");

// Reset chair status to free
$chairReset = request('PATCH', '/api/v1/barbers/chair-status', ['chair_status' => 'free'], ["Authorization: Bearer {$barberToken}"]);
assertTest('7b. Barber Reset Chair Status (free)', in_array($chairReset['code'], [200, 204], true), "Code: {$chairReset['code']}");

// 8. Barber Queue Today Endpoint (GET /api/v1/barbers/my-appointments?date=YYYY-MM-DD)
$today = date('Y-m-d');
$queueRes = request('GET', "/api/v1/barbers/my-appointments?date={$today}", [], ["Authorization: Bearer {$barberToken}"]);
assertTest('8. Barber Live Queue for Today', $queueRes['code'] === 200 && is_array($queueRes['json']['data']), "Code: {$queueRes['code']}, Body: {$queueRes['body']}");

// 9. Barber All Appointments Desk (GET /api/v1/appointments)
$barberApptsRes = request('GET', '/api/v1/appointments', [], ["Authorization: Bearer {$barberToken}"]);
assertTest('9. Barber All Appointments Desk', $barberApptsRes['code'] === 200, "Code: {$barberApptsRes['code']}");

// 10. Barber Schedule & Blackout Periods
$scheduleRes = request('GET', '/api/v1/barbers/schedule', [], ["Authorization: Bearer {$barberToken}"]);
assertTest('10. Barber Weekly Working Schedule', $scheduleRes['code'] === 200, "Code: {$scheduleRes['code']}");

// 11. Customer Cannot Update Barber Chair Status (Role Isolation)
$unauthorizedChair = request('PATCH', '/api/v1/barbers/chair-status', ['chair_status' => 'busy'], ["Authorization: Bearer {$customerToken}"]);
assertTest('11. Customer Forbidden from Barber Chair Update', in_array($unauthorizedChair['code'], [403, 401, 422], true), "Code: {$unauthorizedChair['code']}");

// 12. Password Reset Request Endpoint
$forgotRes = request('POST', '/api/v1/auth/forgot-password', ['email' => 'customer@candycutz.com']);
assertTest('12. Password Reset Request Endpoint', in_array($forgotRes['code'], [200, 422], true), "Code: {$forgotRes['code']}");

echo "\n ALL 12 PHASE 4 MOBILE UNIFICATION TESTS PASSED SUCCESSFULLY!\n";
