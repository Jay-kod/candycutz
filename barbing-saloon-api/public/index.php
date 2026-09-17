<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Serve static uploads and files directly under PHP built-in server
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (preg_match('/^\/(uploads|storage)\//', $requestUri) && ! preg_match('/^\/uploads\/receipts\//', $requestUri)) {
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


// Maintenance mode check
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Read raw input before Laravel captures the request when using PHP's built-in server.
$rawInput = file_get_contents('php://input');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

// Parse request body BEFORE Laravel captures the request
$requestData = [];
if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']) && $rawInput !== false && $rawInput !== '') {
    $cleanInput = ltrim($rawInput, "\xEF\xBB\xBF");
    
    if (str_contains($contentType, 'application/json')) {
        $decoded = json_decode($cleanInput, true);
        if ($decoded !== null) {
            $requestData = $decoded;
        }
    } elseif (str_contains($contentType, 'application/x-www-form-urlencoded')) {
        parse_str($rawInput, $parsed);
        $requestData = $parsed;
    }
}

// Bootstrap Laravel 11 and handle the request
$app = require_once __DIR__ . '/../bootstrap/app.php';

$request = Request::capture();

// Populate request data BEFORE Laravel processes the request
if (!empty($requestData)) {
    $request->request->replace($requestData);
}

$app->handleRequest($request);