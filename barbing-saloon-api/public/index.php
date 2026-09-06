<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Serve static uploads and files directly under PHP built-in server
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (preg_match('/^\/(uploads|storage)\//', $requestUri)) {
    $filePath = __DIR__ . $requestUri;
    if (file_exists($filePath) && is_file($filePath)) {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'svg' => 'image/svg+xml',
        ];
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=86400');
        readfile($filePath);
        exit;
    }
}

// Serve Vue frontend for non-API routes when index.html exists
if (strpos($requestUri, '/api') !== 0 && strpos($requestUri, '/up') !== 0 && strpos($requestUri, '/sanctum') !== 0) {
    if (file_exists(__DIR__ . $requestUri) && is_file(__DIR__ . $requestUri)) {
        return false; // serve requested file as-is
    } elseif (file_exists(__DIR__ . '/index.html')) {
        readfile(__DIR__ . '/index.html');
        exit;
    }
}

// Global CORS headers for mobile & web clients
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Idempotency-Key');
header('Access-Control-Max-Age: 86400');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Maintenance mode check
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel 11 and handle the request
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->handleRequest(Request::capture());
