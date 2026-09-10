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

/**
 * Perform simultaneous parallel HTTP requests using curl_multi.
 */
function parallelRequests(array $requestSpecs): array
{
    global $baseUrl;
    $mh = curl_multi_init();
    $curlHandles = [];

    foreach ($requestSpecs as $key => $spec) {
        $ch = curl_init($baseUrl . $spec['path']);
        $headers = array_merge(['Content-Type: application/json', 'Accept: application/json'], $spec['headers'] ?? []);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $spec['method']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        if (!empty($spec['data']) && in_array($spec['method'], ['POST', 'PUT', 'PATCH'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($spec['data']));
        }

        curl_multi_add_handle($mh, $ch);
        $curlHandles[$key] = $ch;
    }

    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);

    $results = [];
    foreach ($curlHandles as $key => $ch) {
        $raw = curl_multi_getcontent($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $bodyStr = $raw ? substr($raw, $headerSize) : '';
        $json = json_decode($bodyStr, true);

        $results[$key] = [
            'code' => $httpCode,
            'body' => $bodyStr,
            'json' => $json,
        ];

        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }

    curl_multi_close($mh);
    return $results;
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

echo "=== CANDYCUTZ PHASE 5 CONCURRENCY & BUSINESS LOGIC HARDENING SUITE ===\n\n";

// 1. Authenticate Actors
$custLogin = request('POST', '/api/v1/auth/login', [
    'identity' => 'customer@candycutz.com',
    'password' => 'customer123',
    'device_name' => 'mobile-test',
]);
assertTest('1a. Authenticate Customer', $custLogin['code'] === 200 && !empty($custLogin['json']['data']['token']), "Code: {$custLogin['code']}");
$customerToken = $custLogin['json']['data']['token'];

$barberLogin = request('POST', '/api/v1/auth/login', [
    'identity' => 'marcus@candycutz.com',
    'password' => 'barber123',
    'device_name' => 'barber-test',
]);
assertTest('1b. Authenticate Barber', $barberLogin['code'] === 200 && !empty($barberLogin['json']['data']['token']), "Code: {$barberLogin['code']}");
$barberToken = $barberLogin['json']['data']['token'];
$barberId = $barberLogin['json']['data']['barber']['id'] ?? 1;

// Get a service (e.g. standard haircut, ~30-45 mins)
$servicesRes = request('GET', '/api/v1/services');
$service = $servicesRes['json']['data'][0] ?? null;
assertTest('1c. Catalog service resolved', !empty($service['id']), 'No service found');
$serviceId = (int) $service['id'];

// Choose a unique future date for isolation
$dayOffset = 150 + (int) (microtime(true) * 1000 % 300) + random_int(1, 100);
$testDate = date('Y-m-d', strtotime("+{$dayOffset} days"));

// 2. Simultaneous Double-Booking Race Condition Test (Exact Same Slot)
$slotTime1 = '10:00';
$bookingPayload = [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'appointment_date' => $testDate,
    'appointment_time' => $slotTime1,
    'appointment_type' => 'in_shop',
    'payment_method' => 'pay_at_venue',
    'notes' => 'Simultaneous concurrency collision test A',
];

$parallelSpecs = [
    'req1' => [
        'method' => 'POST',
        'path' => '/api/v1/appointments',
        'data' => $bookingPayload,
        'headers' => ["Authorization: Bearer {$customerToken}"],
    ],
    'req2' => [
        'method' => 'POST',
        'path' => '/api/v1/appointments',
        'data' => array_merge($bookingPayload, ['notes' => 'Simultaneous concurrency collision test B']),
        'headers' => ["Authorization: Bearer {$customerToken}"],
    ],
];

$simultaneousResults = parallelRequests($parallelSpecs);
$r1 = $simultaneousResults['req1'];
$r2 = $simultaneousResults['req2'];

$successCount = 0;
$rejectionCount = 0;

foreach ([$r1, $r2] as $res) {
    if ($res['code'] === 201) {
        $successCount++;
    } elseif ($res['code'] === 409 || $res['code'] === 422) {
        $errorCode = $res['json']['error']['code'] ?? '';
        if ($errorCode === 'BOOKING_SLOT_UNAVAILABLE') {
            $rejectionCount++;
        }
    }
}

assertTest('2. Pessimistic lock prevents simultaneous double-booking on same slot',
    $successCount === 1 && $rejectionCount === 1,
    "Expected exactly 1 success (201) and 1 rejection (409/422). Got success={$successCount}, rejection={$rejectionCount}. R1: {$r1['code']} R2: {$r2['code']}"
);

// 3. Overlapping Interval Collision Test
// Service takes duration_minutes (e.g. 30 or 45 mins) starting at 10:00, so 10:15 overlaps!
$overlapBooking = request('POST', '/api/v1/appointments', [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'appointment_date' => $testDate,
    'appointment_time' => '10:15',
    'appointment_type' => 'in_shop',
    'payment_method' => 'pay_at_venue',
], ["Authorization: Bearer {$customerToken}"]);

assertTest('3. Overlapping time interval (10:15 while 10:00-10:30+ is booked) is rejected',
    ($overlapBooking['code'] === 409 || $overlapBooking['code'] === 422) &&
    (($overlapBooking['json']['error']['code'] ?? '') === 'BOOKING_SLOT_UNAVAILABLE'),
    "Code: {$overlapBooking['code']}, Error: " . json_encode($overlapBooking['json'])
);

// 4. Barber Blackout / Blocked Period Enforcement
// Create a blocked period for barber from 13:00 to 14:00 on $testDate
$blockRes = request('POST', '/api/v1/barbers/blocked-periods', [
    'start_datetime' => "{$testDate} 13:00:00",
    'end_datetime' => "{$testDate} 14:00:00",
    'reason' => 'Staff training and maintenance',
], ["Authorization: Bearer {$barberToken}"]);
assertTest('4a. Barber creates blocked period (13:00-14:00)', $blockRes['code'] === 200 || $blockRes['code'] === 201, "Code: {$blockRes['code']}, Body: {$blockRes['body']}");

// Verify availability excludes slots in blocked period
$availRes = request('GET', "/api/v1/availability?date={$testDate}&barber_id={$barberId}&service_id={$serviceId}");
assertTest('4b. GET /api/v1/availability returns 200', $availRes['code'] === 200, "Code: {$availRes['code']}");
$slots = $availRes['json']['data'] ?? [];
$hasBlockedSlot = false;
foreach ($slots as $slot) {
    if (in_array($slot['time'], ['13:00', '13:15', '13:30', '13:45'], true) && ($slot['available'] ?? false)) {
        $hasBlockedSlot = true;
        break;
    }
}
assertTest('4c. Blocked period times are excluded from availability', !$hasBlockedSlot, "Found blocked slot in available list: " . json_encode($slots));

// Customer tries to book in the blocked period anyway
$blockedBooking = request('POST', '/api/v1/appointments', [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'appointment_date' => $testDate,
    'appointment_time' => '13:15',
    'appointment_type' => 'in_shop',
    'payment_method' => 'pay_at_venue',
], ["Authorization: Bearer {$customerToken}"]);
assertTest('4d. Booking during scheduled blocked period fails with BOOKING_SLOT_UNAVAILABLE',
    ($blockedBooking['code'] === 409 || $blockedBooking['code'] === 422) &&
    (($blockedBooking['json']['error']['code'] ?? '') === 'BOOKING_SLOT_UNAVAILABLE'),
    "Code: {$blockedBooking['code']}, Error: " . json_encode($blockedBooking['json'])
);

// 5. Walk-In Concurrency Protection
// Barber records walk-in at 15:00
$walkInRes = request('POST', '/api/v1/barbers/walk-in', [
    'client_name' => 'Walk-In Customer 5',
    'client_phone' => '08012345678',
    'service_id' => $serviceId,
    'appointment_date' => $testDate,
    'appointment_time' => '15:00',
], ["Authorization: Bearer {$barberToken}"]);
assertTest('5a. Barber logs immediate walk-in client (15:00)', $walkInRes['code'] === 201, "Code: {$walkInRes['code']}, Body: {$walkInRes['body']}");

// Customer tries to reserve 15:00 online
$onlineCollision = request('POST', '/api/v1/appointments', [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'appointment_date' => $testDate,
    'appointment_time' => '15:00',
    'appointment_type' => 'in_shop',
    'payment_method' => 'pay_at_venue',
], ["Authorization: Bearer {$customerToken}"]);
assertTest('5b. Online customer is blocked from booking chair occupied by walk-in',
    ($onlineCollision['code'] === 409 || $onlineCollision['code'] === 422) &&
    (($onlineCollision['json']['error']['code'] ?? '') === 'BOOKING_SLOT_UNAVAILABLE'),
    "Code: {$onlineCollision['code']}, Body: {$onlineCollision['body']}"
);

// 6. Stripe Webhook Fulfillment & Idempotency
// First, create a pending booking for payment
$payBookingRes = request('POST', '/api/v1/appointments', [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'appointment_date' => $testDate,
    'appointment_time' => '16:00',
    'appointment_type' => 'in_shop',
    'payment_method' => 'stripe',
    'notes' => 'Stripe webhook verification booking',
], ["Authorization: Bearer {$customerToken}"]);
assertTest('6a. Create pending appointment for Stripe fulfillment', $payBookingRes['code'] === 201, "Code: {$payBookingRes['code']}");
$payAppt = $payBookingRes['json']['data'];
$bookingRef = $payAppt['booking_reference'];
$apptId = $payAppt['id'];

// Simulate Stripe payment_intent.succeeded webhook
$eventId = 'evt_test_' . bin2hex(random_bytes(8));
$intentId = 'pi_test_' . bin2hex(random_bytes(10));
$stripePayload = [
    'id' => $eventId,
    'type' => 'payment_intent.succeeded',
    'data' => [
        'object' => [
            'id' => $intentId,
            'amount_received' => (int) ($payAppt['grand_total'] * 100),
            'currency' => 'ngn',
            'status' => 'succeeded',
            'metadata' => [
                'booking_reference' => $bookingRef,
                'appointment_id' => (string) $apptId,
            ],
        ],
    ],
];

$webhook1 = request('POST', '/api/v1/payments/webhook', $stripePayload);
assertTest('6b. Stripe webhook payment_intent.succeeded received successfully', $webhook1['code'] === 200 && ($webhook1['json']['received'] ?? false) === true, "Code: {$webhook1['code']}, Body: {$webhook1['body']}");

// Verify appointment transitioned to confirmed and deposit_paid = true
$verifiedAppt = request('GET', "/api/v1/appointments/{$apptId}", [], ["Authorization: Bearer {$customerToken}"]);
assertTest('6c. Appointment transitioned to confirmed & deposit_paid',
    $verifiedAppt['code'] === 200 &&
    $verifiedAppt['json']['data']['status'] === 'confirmed' &&
    $verifiedAppt['json']['data']['payment_status'] === 'paid',
    "Status: {$verifiedAppt['json']['data']['status']}, PaymentStatus: {$verifiedAppt['json']['data']['payment_status']}"
);

// Re-send EXACT same webhook event (Idempotency test)
$webhookDuplicate = request('POST', '/api/v1/payments/webhook', $stripePayload);
assertTest('6d. Idempotent re-delivery of identical Stripe webhook succeeds without error',
    $webhookDuplicate['code'] === 200 && ($webhookDuplicate['json']['received'] ?? false) === true,
    "Code: {$webhookDuplicate['code']}, Body: {$webhookDuplicate['body']}"
);

// 7. Cancellation Lifecycle
$cancelRes = request('POST', "/api/v1/appointments/{$apptId}/cancel", [
    'reason' => 'Schedule changed by customer',
], ["Authorization: Bearer {$customerToken}"]);
assertTest('7a. Customer cancels appointment', $cancelRes['code'] === 200 && $cancelRes['json']['data']['status'] === 'cancelled', "Code: {$cancelRes['code']}");

// Attempt to cancel again (must reject invalid transition)
$repeatCancel = request('POST', "/api/v1/appointments/{$apptId}/cancel", [
    'reason' => 'Duplicate cancel',
], ["Authorization: Bearer {$customerToken}"]);
assertTest('7b. Repeat cancellation rejected with INVALID_TRANSITION',
    $repeatCancel['code'] === 422 && ($repeatCancel['json']['error']['code'] ?? '') === 'INVALID_TRANSITION',
    "Code: {$repeatCancel['code']}"
);

// 8. Mail Notification Time Formatting Verification
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$testAppt = \App\Models\Appointment::find($apptId);
$mail = new \App\Mail\BookingConfirmation($testAppt);
$mailContent = $mail->content();
$formattedTime = $mailContent->with['appointmentTime'] ?? '';

assertTest('8. Booking confirmation mail formats time correctly (not 12:00 AM)',
    $formattedTime === '4:00 PM',
    "Expected '4:00 PM', got '{$formattedTime}'"
);

echo "\n ALL PHASE 5 CONCURRENCY & BUSINESS LOGIC HARDENING TESTS PASSED SUCCESSFULLY!\n";
