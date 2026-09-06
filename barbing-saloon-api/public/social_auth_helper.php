<?php

function cc_jwk_rsa_to_pem(array $jwk): ?string
{
    $n = \Firebase\JWT\JWT::urlsafeB64Decode($jwk['n'] ?? '');
    $e = \Firebase\JWT\JWT::urlsafeB64Decode($jwk['e'] ?? '');
    if ($n === '' || $e === '') {
        return null;
    }

    $modulus = cc_asn1_integer($n);
    $exponent = cc_asn1_integer($e);
    $sequence = cc_asn1_sequence($modulus . $exponent);
    $der = "30" . cc_asn1_length(strlen($sequence) / 2) . $sequence;

    $pem = "-----BEGIN PUBLIC KEY-----\r\n"
        . chunk_split(base64_encode(hex2bin($der)), 64, "\r\n")
        . "-----END PUBLIC KEY-----\r\n";
    return $pem;
}

function cc_asn1_length(int $length): string
{
    if ($length < 0x80) {
        return sprintf("%02x", $length);
    }
    $hex = '';
    while ($length > 0) {
        $hex = sprintf("%02x", $length & 0xff) . $hex;
        $length >>= 8;
    }
    return sprintf("%02x", 0x80 | strlen($hex) / 2) . $hex;
}

function cc_asn1_integer(string $bytes): string
{
    $unpadded = ltrim($bytes, "\x00");
    if ($unpadded === '' || (ord($unpadded[0]) & 0x80) !== 0) {
        $unpadded = "\x00" . $unpadded;
    }
    return "02" . cc_asn1_length(strlen($unpadded)) . bin2hex($unpadded);
}

function cc_asn1_sequence(string $payloadHex): string
{
    return "30" . cc_asn1_length(strlen($payloadHex) / 2) . $payloadHex;
}

function cc_fetch_jwks(string $uri): array
{
    $cacheDir = sys_get_temp_dir() . '/candycutz_jwks';
    $cacheFile = $cacheDir . '/' . md5($uri) . '.json';
    if (is_file($cacheFile)) {
        $cached = json_decode((string) file_get_contents($cacheFile), true);
        if (is_array($cached) && isset($cached['expires']) && $cached['expires'] > time()) {
            return $cached['keys'];
        }
    }

    $ctx = stream_context_create(['http' => ['timeout' => 15, 'user_agent' => 'CandyCutz/1.0']]);
    $raw = @file_get_contents($uri, false, $ctx);
    if ($raw === false) {
        return [];
    }
    $payload = json_decode($raw, true);
    $keys = is_array($payload) && isset($payload['keys']) ? $payload['keys'] : [];

    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }
    @file_put_contents($cacheFile, json_encode(['expires' => time() + 3600, 'keys' => $keys]));

    return $keys;
}

function cc_verify_google_id_token(string $idToken): ?array
{
    $keys = cc_fetch_jwks('https://www.googleapis.com/oauth2/v3/certs');
    $payload = null;
    foreach ($keys as $jwk) {
        $pem = cc_jwk_rsa_to_pem($jwk);
        if (!$pem) continue;
        try {
            $decoded = \Firebase\JWT\JWT::decode($idToken, new \Firebase\JWT\Key($pem, 'RS256'));
            $payload = (array) $decoded;
            break;
        } catch (Throwable $e) {
            continue;
        }
    }
    if ($payload === null) {
        return null;
    }
    if (($payload['iss'] ?? '') !== 'https://accounts.google.com') {
        return null;
    }
    return $payload;
}

function cc_verify_apple_id_token(string $idToken): ?array
{
    $keys = cc_fetch_jwks('https://appleid.apple.com/auth/keys');
    $payload = null;
    foreach ($keys as $jwk) {
        $alg = $jwk['alg'] ?? 'RS256';
        $pem = cc_jwk_rsa_to_pem($jwk);
        if (!$pem) continue;
        try {
            $decoded = \Firebase\JWT\JWT::decode($idToken, new \Firebase\JWT\Key($pem, $alg));
            $payload = (array) $decoded;
            break;
        } catch (Throwable $e) {
            continue;
        }
    }
    if ($payload === null) {
        return null;
    }
    if (($payload['iss'] ?? '') !== 'https://appleid.apple.com') {
        return null;
    }
    return $payload;
}