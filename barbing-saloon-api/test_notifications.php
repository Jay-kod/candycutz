<?php
$ch = curl_init('http://localhost:8000/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'customer@candycutz.com',
    'password' => 'password',
]));
$res = curl_exec($ch);
$data = json_decode($res, true);
$token = $data['data']['token'] ?? '';
curl_close($ch);

echo "Token: " . substr($token, 0, 20) . "...\n";

$ch2 = curl_init('http://localhost:8000/api/notifications');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
$res2 = curl_exec($ch2);
$code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "Status: $code2\n";
echo "Response: $res2\n";
