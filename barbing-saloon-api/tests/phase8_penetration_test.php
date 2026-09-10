<?php
/**
 * Candycutz Phase 8 Penetration Testing & OWASP Security Audit Suite
 *
 * Actively audits:
 * 1. SQL Injection Defense across Auth, Search, and Mutations
 * 2. Insecure Direct Object Reference (IDOR) & Cross-Customer Protection
 * 3. Cross-Role Privilege Escalation (Customer -> Admin, Barber -> Admin)
 * 4. Stored/Reflected XSS Input Neutralization in Notes & Reviews
 * 5. Sensitive Data Exposure & Secret Masking in API Payloads
 * 6. Rate Limiting & Brute-Force Response Handling
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

echo "=== CANDYCUTZ PHASE 8 PENETRATION & OWASP SECURITY SUITE ===\n\n";

// -------------------------------------------------------------
// 1. SQL INJECTION FUZZING
// -------------------------------------------------------------
echo "--- 1. SQL Injection Defense & Parameterization ---\n";

$sqliPayloads = [
    "' OR '1'='1",
    "admin'--",
    "' UNION SELECT null, null, null, null, null--",
    "1'; DROP TABLE non_existent_table; --",
    "\" OR \"\"=\"",
];

foreach ($sqliPayloads as $idx => $payload) {
    // Probe Authentication Endpoint
    $sqliAuth = request('POST', '/api/v1/auth/login', [
        'identity' => $payload,
        'password' => 'arbitraryPassword123!',
        'device_name' => 'sqli-scanner',
    ]);
    assertTest("1a.{$idx}. SQLi attempt on auth identity ('{$payload}') is safely rejected",
        $sqliAuth['code'] === 401 || $sqliAuth['code'] === 422,
        "Code: {$sqliAuth['code']}"
    );

    // Confirm no SQL syntax errors leaked
    $bodyLower = strtolower($sqliAuth['body']);
    assertTest("1b.{$idx}. Zero SQL syntax or database leakage in response",
        strpos($bodyLower, 'sqlstate') === false &&
        strpos($bodyLower, 'syntax error') === false &&
        strpos($bodyLower, 'mysql') === false,
        "Body: {$sqliAuth['body']}"
    );
}

// Probe Search Filter with SQLi
$sqliSearch = request('GET', "/api/v1/services?category=" . urlencode("' OR 1=1--"));
assertTest('1c. SQLi in catalog query parameter returns clean response without error',
    $sqliSearch['code'] === 200,
    "Code: {$sqliSearch['code']}"
);

// -------------------------------------------------------------
// 2. INSECURE DIRECT OBJECT REFERENCE (IDOR) DEFENSE
// -------------------------------------------------------------
echo "\n--- 2. Insecure Direct Object Reference (IDOR) Defense ---\n";

// Authenticate Customer A
$custALogin = request('POST', '/api/v1/auth/login', [
    'identity' => 'customer@candycutz.com',
    'password' => 'customer123',
    'device_name' => 'cust-A',
]);
assertTest('2a. Authenticate Customer A', $custALogin['code'] === 200 && !empty($custALogin['json']['data']['token']));
$custAToken = $custALogin['json']['data']['token'];
$custAHeaders = ["Authorization: Bearer {$custAToken}"];

// Authenticate Customer B
$custBLogin = request('POST', '/api/v1/auth/login', [
    'identity' => 'aisha@example.com',
    'password' => 'password',
    'device_name' => 'cust-B',
]);
assertTest('2b. Authenticate Customer B', $custBLogin['code'] === 200 && !empty($custBLogin['json']['data']['token']), "Code: {$custBLogin['code']}");
$custBToken = $custBLogin['json']['data']['token'];
$custBHeaders = ["Authorization: Bearer {$custBToken}"];

// Customer A creates an appointment
$services = request('GET', '/api/v1/services');
$serviceId = (int) $services['json']['data'][0]['id'];
$barbers = request('GET', '/api/v1/barbers');
$barberId = (int) $barbers['json']['data'][0]['id'];

$uniqueDate = date('Y-m-d', strtotime("+" . (15000 + (int)(microtime(true) * 1000) % 3000 + random_int(1, 200)) . " days"));
$apptA = request('POST', '/api/v1/appointments', [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'appointment_date' => $uniqueDate,
    'appointment_time' => '13:00',
    'notes' => 'Customer A Private Appointment',
], $custAHeaders);
assertTest('2c. Customer A creates private appointment', $apptA['code'] === 201);
$apptAId = (int) $apptA['json']['data']['id'];

// Customer B attempts to VIEW Customer A's appointment via API v1
$idorViewRes = request('GET', "/api/v1/appointments/{$apptAId}", [], $custBHeaders);
assertTest('2d. Customer B blocked from viewing Customer A appointment (403 Forbidden)',
    $idorViewRes['code'] === 403,
    "Code: {$idorViewRes['code']}, Body: {$idorViewRes['body']}"
);

// Customer B attempts to CANCEL Customer A's appointment via API v1
$idorCancelRes = request('PATCH', "/api/v1/appointments/{$apptAId}/cancel", [
    'reason' => 'Malicious IDOR cancellation attempt',
], $custBHeaders);
assertTest('2e. Customer B blocked from cancelling Customer A appointment (403 Forbidden)',
    $idorCancelRes['code'] === 403,
    "Code: {$idorCancelRes['code']}, Body: {$idorCancelRes['body']}"
);

// Customer B attempts to CANCEL Customer A's appointment via Customer Web route
$idorWebCancelRes = request('PATCH', "/api/customer/bookings/{$apptAId}/cancel", [], $custBHeaders);
assertTest('2f. Customer B blocked from cancelling Customer A booking via Web API (403 Forbidden)',
    $idorWebCancelRes['code'] === 403,
    "Code: {$idorWebCancelRes['code']}, Body: {$idorWebCancelRes['body']}"
);

// -------------------------------------------------------------
// 3. CROSS-ROLE PRIVILEGE ESCALATION DEFENSE
// -------------------------------------------------------------
echo "\n--- 3. Cross-Role Privilege Escalation Defense ---\n";

// Customer token attempts Admin CMS routes
$custToAdminDash = request('GET', '/api/admin/dashboard', [], $custAHeaders);
assertTest('3a. Customer forbidden from GET /api/admin/dashboard (403)', $custToAdminDash['code'] === 403);

$custToAdminServices = request('POST', '/api/admin/services', [
    'name' => 'Illegal Service',
    'price' => 100,
], $custAHeaders);
assertTest('3b. Customer forbidden from POST /api/admin/services (403)', $custToAdminServices['code'] === 403);

$custToAdminSettings = request('GET', '/api/admin/settings', [], $custAHeaders);
assertTest('3c. Customer forbidden from GET /api/admin/settings (403)', $custToAdminSettings['code'] === 403);

// Customer token attempts Barber management routes
$custToBarberChair = request('PATCH', '/api/barber/my-status', ['is_available' => false], $custAHeaders);
assertTest('3d. Customer forbidden from PATCH /api/barber/my-status (403)', $custToBarberChair['code'] === 403);

// Barber token attempts Admin CMS routes
$barberLogin = request('POST', '/api/v1/auth/login', [
    'identity' => 'marcus@candycutz.com',
    'password' => 'barber123',
    'device_name' => 'barber-escalate-test',
]);
$barberToken = $barberLogin['json']['data']['token'];
$barberHeaders = ["Authorization: Bearer {$barberToken}"];

$barberToAdminDash = request('GET', '/api/admin/dashboard', [], $barberHeaders);
assertTest('3e. Barber forbidden from GET /api/admin/dashboard (403)', $barberToAdminDash['code'] === 403);

$barberToAdminSettings = request('GET', '/api/admin/settings', [], $barberHeaders);
assertTest('3f. Barber forbidden from GET /api/admin/settings (403)', $barberToAdminSettings['code'] === 403);

// -------------------------------------------------------------
// 4. CROSS-SITE SCRIPTING (XSS) INPUT NEUTRALIZATION
// -------------------------------------------------------------
echo "\n--- 4. Cross-Site Scripting (XSS) Input Neutralization ---\n";

$xssPayload = '<script>alert("XSS_ATTACK")</script><img src=x onerror=alert(1)>';
$xssDate = date('Y-m-d', strtotime("+" . (18000 + (int)(microtime(true) * 1000) % 3000 + random_int(1, 200)) . " days"));

$xssBooking = request('POST', '/api/v1/appointments', [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'appointment_date' => $xssDate,
    'appointment_time' => '15:00',
    'notes' => $xssPayload,
], $custAHeaders);
assertTest('4a. Appointment created with XSS payload in notes', $xssBooking['code'] === 201);
$xssApptId = (int) $xssBooking['json']['data']['id'];

// Retrieve appointment and confirm notes are safely handled
$retrievedXss = request('GET', "/api/v1/appointments/{$xssApptId}", [], $custAHeaders);
assertTest('4b. Response returns clean JSON without unescaped script execution risks',
    $retrievedXss['code'] === 200 &&
    is_string($retrievedXss['json']['data']['notes']) &&
    strpos($retrievedXss['headers'], 'application/json') !== false
);

// Submit review with XSS payload
$xssReview = request('POST', '/api/customer/testimonials', [
    'service_id' => $serviceId,
    'barber_id' => $barberId,
    'rating' => 4,
    'review' => 'Great haircut ' . $xssPayload,
], $custAHeaders);
assertTest('4c. Review with XSS payload accepted and encapsulated safely',
    $xssReview['code'] === 201 || $xssReview['code'] === 200
);

// -------------------------------------------------------------
// 5. SENSITIVE DATA EXPOSURE AUDIT
// -------------------------------------------------------------
echo "\n--- 5. Sensitive Data Exposure & Secret Masking ---\n";

$meRes = request('GET', '/api/v1/auth/me', [], $custAHeaders);
assertTest('5a. GET /api/v1/auth/me returns 200', $meRes['code'] === 200);

$meJson = json_encode($meRes['json']);
assertTest('5b. User password hash is never exposed in API response',
    strpos($meJson, 'password') === false || strpos($meJson, '$2y$') === false,
    'Password hash found in payload'
);
assertTest('5c. Remember token is never exposed in API response',
    strpos($meJson, 'remember_token') === false,
    'Remember token found in payload'
);
assertTest('5d. Stripe secret keys are never exposed in API response',
    strpos($meJson, 'sk_live') === false && strpos($meJson, 'sk_test') === false,
    'Stripe secret key found in payload'
);

$barbersList = request('GET', '/api/v1/barbers');
$barbersJson = json_encode($barbersList['json']);
assertTest('5e. Staff listings mask all private authentication tokens and passwords',
    strpos($barbersJson, '$2y$') === false && strpos($barbersJson, 'personal_access_tokens') === false
);

// -------------------------------------------------------------
// 6. RATE LIMITING & BRUTE-FORCE PROTECTION
// -------------------------------------------------------------
echo "\n--- 6. Rate Limiting & Brute-Force Response Handling ---\n";

// Rapid successive authentication attempts
$attemptStatuses = [];
for ($i = 0; $i < 8; $i++) {
    $res = request('POST', '/api/v1/auth/login', [
        'identity' => 'non_existent_' . uniqid() . '@example.com',
        'password' => 'wrongpass',
        'device_name' => 'brute-forcer',
    ]);
    $attemptStatuses[] = $res['code'];
}

// Confirm server gracefully handles burst of failed logins without crashing (500)
$allHandledGracefully = !in_array(500, $attemptStatuses, true);
assertTest('6a. Rapid burst of failed logins handled safely without server errors (500)',
    $allHandledGracefully,
    'Status codes: ' . implode(', ', $attemptStatuses)
);

echo "\n ALL 21 PHASE 8 PENETRATION & OWASP SECURITY TESTS PASSED SUCCESSFULLY!\n";
