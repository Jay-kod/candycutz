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

echo "=== CANDYCUTZ PHASE 2 AUTHENTICATION SUITE ===\n\n";

// 1. Health Check
$res = request('GET', '/api/health');
assertTest('1. Health Check', $res['code'] === 200, "Code: {$res['code']}");

// 2. CORS Headers
$res = request('OPTIONS', '/api/v1/auth/login', [], ['Origin: http://localhost:5174']);
assertTest('2. CORS Preflight Support', str_contains($res['headers'], 'Access-Control-Allow-Origin: http://localhost:5174') || str_contains($res['headers'], 'access-control-allow-origin: http://localhost:5174'), "Headers: {$res['headers']}");
assertTest('2b. CORS Credentials Supported', str_contains($res['headers'], 'Access-Control-Allow-Credentials: true') || str_contains($res['headers'], 'access-control-allow-credentials: true'), "Headers: {$res['headers']}");

// 3. Register New User
$testEmail = "testuser_{$timestamp}@candycutz.com";
$testUsername = "test_{$timestamp}";
$testPhone = "0812" . substr((string)$timestamp, -7);
$testPass = "Password123!";

$regData = [
    'name' => 'Phase Two Test',
    'username' => "@{$testUsername}",
    'email' => $testEmail,
    'phone' => "+234 " . substr($testPhone, 1, 3) . " " . substr($testPhone, 4),
    'password' => $testPass,
    'password_confirmation' => $testPass,
    'device_name' => 'web-client',
];

$res = request('POST', '/api/v1/auth/register', $regData);
assertTest('3. Register with @username and formatted phone', $res['code'] === 201 && !empty($res['json']['data']['token']), "Code: {$res['code']}, Body: {$res['body']}");
$user = $res['json']['data']['user'];
assertTest('3b. Username stripped of leading @ and lowercased', $user['username'] === $testUsername, "Got: " . ($user['username'] ?? 'null'));

// 4. Login with Email (Device A: Web Client)
$loginResWeb = request('POST', '/api/auth/login', [
    'email' => $testEmail,
    'password' => $testPass,
    'device_name' => 'web-client',
]);
assertTest('4. Login with Email', $loginResWeb['code'] === 200 && !empty($loginResWeb['json']['data']['token']), "Code: {$loginResWeb['code']}");
$webToken = $loginResWeb['json']['data']['token'];

// 5. Login with @Username (Device B: Mobile Client)
$loginResMobile = request('POST', '/api/v1/auth/login', [
    'identity' => "@{$testUsername}",
    'password' => $testPass,
    'device_name' => 'mobile-client',
]);
assertTest('5. Login with @Username on Mobile endpoint', $loginResMobile['code'] === 200 && !empty($loginResMobile['json']['data']['token']), "Code: {$loginResMobile['code']}");
$mobileToken = $loginResMobile['json']['data']['token'];

// 6. Login with Nigerian Phone number
$loginResPhone = request('POST', '/api/v1/auth/login', [
    'identity' => $testPhone,
    'password' => $testPass,
    'device_name' => 'tablet-client',
]);
assertTest('6. Login with Phone Number (080 format)', $loginResPhone['code'] === 200 && !empty($loginResPhone['json']['data']['token']), "Code: {$loginResPhone['code']}");
$tabletToken = $loginResPhone['json']['data']['token'];

// 7. Concurrent Multi-Device Session Isolation (CRITICAL TEST)
// Both web and mobile tokens must be active simultaneously!
$meWeb = request('GET', '/api/auth/me', [], ["Authorization: Bearer {$webToken}"]);
assertTest('7a. Web token active concurrently', $meWeb['code'] === 200, "Code: {$meWeb['code']}");

$meMobile = request('GET', '/api/v1/auth/me', [], ["Authorization: Bearer {$mobileToken}"]);
assertTest('7b. Mobile token active concurrently (Zero Collision)', $meMobile['code'] === 200, "Code: {$meMobile['code']}");

// 8. Device-Specific Logout Isolation
// Logging out on web must ONLY revoke web token, keeping mobile token alive!
$logoutWeb = request('POST', '/api/auth/logout', [], ["Authorization: Bearer {$webToken}"]);
assertTest('8a. Web logout succeeds', $logoutWeb['code'] === 200, "Code: {$logoutWeb['code']}");

$checkWebRevoked = request('GET', '/api/auth/me', [], ["Authorization: Bearer {$webToken}"]);
assertTest('8b. Web token is now revoked (401)', $checkWebRevoked['code'] === 401, "Code: {$checkWebRevoked['code']}");

$checkMobileStillAlive = request('GET', '/api/v1/auth/me', [], ["Authorization: Bearer {$mobileToken}"]);
assertTest('8c. Mobile token remains valid after Web logout', $checkMobileStillAlive['code'] === 200, "Code: {$checkMobileStillAlive['code']}");

// 9. Social Login - Google (Auto-provisioning)
$googleSub = "google_sub_" . $timestamp;
$socialGoogle = request('POST', '/api/v1/auth/social-login', [
    'provider' => 'google',
    'id_token' => "mock.{$googleSub}.sig",
    'device_name' => 'mobile-app',
    'user_data' => ['name' => 'Google VIP Guest'],
]);
assertTest('9. Google Social Login creates user & issues token', $socialGoogle['code'] === 200 && !empty($socialGoogle['json']['data']['token']), "Code: {$socialGoogle['code']}, Body: {$socialGoogle['body']}");
$googleUser = $socialGoogle['json']['data']['user'];
assertTest('9b. Google user generated unique username', !empty($googleUser['username']), "Username: " . ($googleUser['username'] ?? 'null'));

// 10. Social Login - Apple (Auto-provisioning)
$appleSub = "apple_sub_" . $timestamp;
$socialApple = request('POST', '/api/auth/social-login', [
    'provider' => 'apple',
    'id_token' => "mock.{$appleSub}.sig",
    'device_name' => 'web-client',
    'user_data' => ['name' => 'Apple VIP Guest'],
]);
assertTest('10. Apple Social Login creates user & issues token', $socialApple['code'] === 200 && !empty($socialApple['json']['data']['token']), "Code: {$socialApple['code']}, Body: {$socialApple['body']}");

// 11. Forgot Password Flow
$forgotRes = request('POST', '/api/v1/auth/forgot-password', [
    'email' => $testEmail,
]);
assertTest('11. Forgot password accepts email', $forgotRes['code'] === 200, "Code: {$forgotRes['code']}");

// 12. Password Reset using token directly from DB
require_once dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$resetRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $testEmail)->first();
assertTest('12a. Password reset token stored in DB', $resetRecord !== null, "No reset record found in DB");

// In AuthService::forgotPassword, token is random(60) and hashed in DB.
// Let's create a known raw token for the test:
$knownRawToken = \Illuminate\Support\Str::random(60);
\Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
    ['email' => $testEmail],
    ['token' => \Illuminate\Support\Facades\Hash::make($knownRawToken), 'created_at' => now()]
);

$newPass = "NewSecurePassword123!";
$resetRes = request('POST', '/api/v1/auth/reset-password', [
    'email' => $testEmail,
    'token' => $knownRawToken,
    'password' => $newPass,
    'password_confirmation' => $newPass,
]);
assertTest('12b. Reset password with token succeeds', $resetRes['code'] === 200 && !empty($resetRes['json']['data']['token']), "Code: {$resetRes['code']}, Body: {$resetRes['body']}");

// Confirm login works with new password
$loginWithNewPass = request('POST', '/api/v1/auth/login', [
    'email' => $testEmail,
    'password' => $newPass,
]);
assertTest('12c. Login with new password succeeds', $loginWithNewPass['code'] === 200, "Code: {$loginWithNewPass['code']}");
$resetUserToken = $loginWithNewPass['json']['data']['token'];

// 13. Change Password (Authenticated)
$finalPass = "FinalPassword123!";
$changePassRes = request('POST', '/api/v1/auth/change-password', [
    'current_password' => $newPass,
    'new_password' => $finalPass,
    'new_password_confirmation' => $finalPass,
], ["Authorization: Bearer {$resetUserToken}"]);
assertTest('13. Authenticated change-password succeeds', $changePassRes['code'] === 200, "Code: {$changePassRes['code']}, Body: {$changePassRes['body']}");

// 14. Logout-All Devices
$logoutAllRes = request('POST', '/api/v1/auth/logout-all', [], ["Authorization: Bearer {$resetUserToken}"]);
assertTest('14a. Logout-all succeeds', $logoutAllRes['code'] === 200, "Code: {$logoutAllRes['code']}");

$checkAllRevoked = request('GET', '/api/v1/auth/me', [], ["Authorization: Bearer {$resetUserToken}"]);
assertTest('14b. Token revoked after logout-all', $checkAllRevoked['code'] === 401, "Code: {$checkAllRevoked['code']}");

echo "\n>>> ALL 14 SCENARIOS PASSED WITH ZERO REGRESSIONS! <<<\n";
