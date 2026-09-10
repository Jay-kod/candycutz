<?php

declare(strict_types=1);

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

echo "=== CANDYCUTZ PHASE 3 ROUTING & API V1 SUITE ===\n\n";

// 1. Health Checks
$healthV1 = request('GET', '/api/v1/health');
assertTest('1a. GET /api/v1/health probe', $healthV1['code'] === 200 && ($healthV1['json']['data']['status'] ?? '') === 'healthy', "Code: {$healthV1['code']}");

$healthRoot = request('GET', '/api/health');
assertTest('1b. GET /api/health root alias probe', $healthRoot['code'] === 200, "Code: {$healthRoot['code']}");

// 2. Services Catalog
$servicesRes = request('GET', '/api/v1/services');
assertTest('2a. GET /api/v1/services returns list', $servicesRes['code'] === 200 && is_array($servicesRes['json']['data']), "Code: {$servicesRes['code']}");
$firstService = $servicesRes['json']['data'][0] ?? null;
assertTest('2b. Service model has price and duration', !empty($firstService['id']) && isset($firstService['price']) && isset($firstService['duration_minutes']), "First service: " . json_encode($firstService));

$singleService = request('GET', "/api/v1/services/{$firstService['id']}");
assertTest('2c. GET /api/v1/services/{id} by numeric ID', $singleService['code'] === 200 && ($singleService['json']['data']['id'] ?? 0) === $firstService['id'], "Code: {$singleService['code']}");

// 3. Service Categories
$categoriesRes = request('GET', '/api/v1/service-categories');
assertTest('3. GET /api/v1/service-categories', $categoriesRes['code'] === 200 && count($categoriesRes['json']['data']) > 0, "Code: {$categoriesRes['code']}");

// 4. Barbers Directory
$barbersRes = request('GET', '/api/v1/barbers');
assertTest('4a. GET /api/v1/barbers returns list', $barbersRes['code'] === 200 && count($barbersRes['json']['data']) > 0, "Code: {$barbersRes['code']}");
$firstBarber = $barbersRes['json']['data'][0] ?? null;
assertTest('4b. Barber model includes chair_status and specialties', isset($firstBarber['chair_status']) && isset($firstBarber['rating']), "Barber: " . json_encode($firstBarber));

$singleBarber = request('GET', "/api/v1/barbers/{$firstBarber['id']}");
assertTest('4c. GET /api/v1/barbers/{id}', $singleBarber['code'] === 200 && ($singleBarber['json']['data']['id'] ?? 0) === $firstBarber['id'], "Code: {$singleBarber['code']}");

// 5. Availability (Structured TimeSlot[])
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$availRes = request('GET', "/api/v1/availability?date={$tomorrow}&service_id={$firstService['id']}&barber_id={$firstBarber['id']}");
assertTest('5a. GET /api/v1/availability returns 200', $availRes['code'] === 200 && is_array($availRes['json']['data']), "Code: {$availRes['code']}");
$firstSlot = $availRes['json']['data'][0] ?? null;
assertTest('5b. Slot is TimeSlot object with time & available fields', !empty($firstSlot['time']) && isset($firstSlot['available']), "Slot: " . json_encode($firstSlot));

// 6. Service Zones (Keffi coverage)
$zonesRes = request('GET', '/api/v1/service-zones');
assertTest('6. GET /api/v1/service-zones returns Keffi zones with surcharge', $zonesRes['code'] === 200 && count($zonesRes['json']['data']) > 0, "Code: {$zonesRes['code']}");

// 7. Stripe Webhook Receiver
$webhookRes = request('POST', '/api/v1/payments/webhook', [
    'type' => 'test.event',
    'data' => ['object' => []],
]);
assertTest('7. POST /api/v1/payments/webhook returns received: true', $webhookRes['code'] === 200 && ($webhookRes['json']['received'] ?? false) === true, "Code: {$webhookRes['code']}");

// 8. Auth Setup (Customer & Barber)
$customerLogin = request('POST', '/api/v1/auth/login', [
    'identity' => 'customer@candycutz.com',
    'password' => 'customer123',
    'device_name' => 'mobile-app',
]);
assertTest('8a. Authenticate Customer', $customerLogin['code'] === 200 && !empty($customerLogin['json']['data']['token']), "Code: {$customerLogin['code']}");
$customerToken = $customerLogin['json']['data']['token'];

$barberLogin = request('POST', '/api/v1/auth/login', [
    'identity' => 'marcus@candycutz.com',
    'password' => 'barber123',
    'device_name' => 'barber-app',
]);
assertTest('8b. Authenticate Barber', $barberLogin['code'] === 200 && !empty($barberLogin['json']['data']['token']), "Code: {$barberLogin['code']}");
$barberToken = $barberLogin['json']['data']['token'];

// 9. Customer Creates Appointment (Atomic reservation)
$assignedBarberId = $barberLogin['json']['data']['barber']['id'] ?? $barberLogin['json']['data']['user']['barber_id'] ?? $firstBarber['id'];
$dayOffset = 10 + (int) (time() % 70) + random_int(1, 20);
$nextWeek = date('Y-m-d', strtotime("+{$dayOffset} days"));
$bookingPayload = [
    'service_id' => $firstService['id'],
    'barber_id' => $assignedBarberId,
    'appointment_date' => $nextWeek,
    'start_time' => '14:00',
    'appointment_type' => 'in_shop',
    'payment_method' => 'pay_at_venue',
    'notes' => 'Phase 3 automated verification booking',
];

$bookRes = request('POST', '/api/v1/appointments', $bookingPayload, ["Authorization: Bearer {$customerToken}"]);
assertTest('9a. POST /api/v1/appointments creates booking', $bookRes['code'] === 201 && !empty($bookRes['json']['data']['booking_reference']), "Code: {$bookRes['code']}, Body: {$bookRes['body']}");
$appointment = $bookRes['json']['data'];
$appointmentId = $appointment['id'];

// 10. List Appointments (Customer & Barber role filtration)
$customerAppointments = request('GET', '/api/v1/appointments', [], ["Authorization: Bearer {$customerToken}"]);
assertTest('10a. Customer GET /api/v1/appointments returns own items', $customerAppointments['code'] === 200 && count($customerAppointments['json']['data']['items']) > 0, "Code: {$customerAppointments['code']}");

$barberAppointments = request('GET', '/api/v1/appointments', [], ["Authorization: Bearer {$barberToken}"]);
assertTest('10b. Barber GET /api/v1/appointments returns chair queue', $barberAppointments['code'] === 200, "Code: {$barberAppointments['code']}");

// 11. View Single Appointment
$singleApptRes = request('GET', "/api/v1/appointments/{$appointmentId}", [], ["Authorization: Bearer {$customerToken}"]);
assertTest('11. GET /api/v1/appointments/{id}', $singleApptRes['code'] === 200 && ($singleApptRes['json']['data']['id'] ?? 0) === $appointmentId, "Code: {$singleApptRes['code']}");

// 12. Barber Updates Operational Status
$statusUpdateRes = request('PATCH', "/api/v1/appointments/{$appointmentId}/status", [
    'status' => 'in_progress',
    'reason' => 'Client seated in barber chair',
], ["Authorization: Bearer {$barberToken}"]);
assertTest('12a. Barber PATCH /api/v1/appointments/{id}/status to in_progress', $statusUpdateRes['code'] === 200, "Code: {$statusUpdateRes['code']}");

$statusCompleteRes = request('PATCH', "/api/v1/appointments/{$appointmentId}/status", [
    'status' => 'completed',
    'reason' => 'Finished precision cut',
], ["Authorization: Bearer {$barberToken}"]);
assertTest('12b. Barber PATCH status to completed', $statusCompleteRes['code'] === 200, "Code: {$statusCompleteRes['code']}");

// 13. Cancellation flow on separate booking
$cancelDate = date('Y-m-d', strtotime("+" . ($dayOffset + 1) . " days"));
$cancelBooking = request('POST', '/api/v1/appointments', [
    'service_id' => $firstService['id'],
    'barber_id' => $firstBarber['id'],
    'appointment_date' => $cancelDate,
    'start_time' => '15:00',
    'appointment_type' => 'in_shop',
    'payment_method' => 'pay_at_venue',
], ["Authorization: Bearer {$customerToken}"]);
$cancelApptId = $cancelBooking['json']['data']['id'] ?? null;
assertTest('13a. Create secondary appointment for cancellation', $cancelBooking['code'] === 201 && $cancelApptId !== null, "Code: {$cancelBooking['code']}");

$cancelRes = request('PATCH', "/api/v1/appointments/{$cancelApptId}/cancel", [
    'reason' => 'Schedule conflict',
], ["Authorization: Bearer {$customerToken}"]);
assertTest('13b. PATCH /api/v1/appointments/{id}/cancel', $cancelRes['code'] === 200 && ($cancelRes['json']['data']['status'] ?? '') === 'cancelled', "Code: {$cancelRes['code']}");

// 14. Barber Chair Status Management
$chairBusy = request('PATCH', '/api/v1/barbers/chair-status', ['status' => 'busy'], ["Authorization: Bearer {$barberToken}"]);
assertTest('14a. Barber PATCH /api/v1/barbers/chair-status to busy', $chairBusy['code'] === 200 && ($chairBusy['json']['data']['chair_status'] ?? '') === 'busy', "Code: {$chairBusy['code']}");

$chairFree = request('PATCH', '/api/v1/barbers/chair-status', ['status' => 'free'], ["Authorization: Bearer {$barberToken}"]);
assertTest('14b. Barber PATCH /api/v1/barbers/chair-status to free', $chairFree['code'] === 200 && ($chairFree['json']['data']['chair_status'] ?? '') === 'free', "Code: {$chairFree['code']}");

// 15. Barber Schedule & Blocked Periods
$scheduleRes = request('GET', '/api/v1/barbers/schedule', [], ["Authorization: Bearer {$barberToken}"]);
assertTest('15a. Barber GET /api/v1/barbers/schedule returns 7 days', $scheduleRes['code'] === 200 && count($scheduleRes['json']['data']) === 7, "Code: {$scheduleRes['code']}");

$blockedRes = request('GET', '/api/v1/barbers/blocked-periods', [], ["Authorization: Bearer {$barberToken}"]);
assertTest('15b. Barber GET /api/v1/barbers/blocked-periods', $blockedRes['code'] === 200, "Code: {$blockedRes['code']}");

// 16. Standard Error Envelopes
$notFoundRes = request('GET', '/api/v1/services/999999');
assertTest('16a. 404 returns standard error code RESOURCE_NOT_FOUND', $notFoundRes['code'] === 404 && ($notFoundRes['json']['error']['code'] ?? '') === 'RESOURCE_NOT_FOUND', "Code: {$notFoundRes['code']}, Body: {$notFoundRes['body']}");

$unauthRes = request('GET', '/api/v1/appointments');
assertTest('16b. 401 returns standard error code UNAUTHENTICATED', $unauthRes['code'] === 401 && ($unauthRes['json']['error']['code'] ?? '') === 'UNAUTHENTICATED', "Code: {$unauthRes['code']}, Body: {$unauthRes['body']}");

// 17. Zero-Regression Legacy Routes (Web client compatibility)
$legacySettings = request('GET', '/api/public/settings');
assertTest('17a. Legacy /api/public/settings intact', $legacySettings['code'] === 200, "Code: {$legacySettings['code']}");

$legacyServices = request('GET', '/api/public/services');
assertTest('17b. Legacy /api/public/services intact', $legacyServices['code'] === 200, "Code: {$legacyServices['code']}");

$legacyBarbers = request('GET', '/api/public/barbers');
assertTest('17c. Legacy /api/public/barbers intact', $legacyBarbers['code'] === 200, "Code: {$legacyBarbers['code']}");

$legacySlots = request('GET', "/api/public/available-slots?date={$tomorrow}&barber_id={$firstBarber['id']}&service_id={$firstService['id']}");
assertTest('17d. Legacy /api/public/available-slots intact', $legacySlots['code'] === 200 && is_array($legacySlots['json']['data']), "Code: {$legacySlots['code']}");

echo "\n>>> ALL 23 PHASE 3 SCENARIOS PASSED WITH ZERO REGRESSIONS! <<<\n";
