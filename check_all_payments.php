<?php
$host = '127.0.0.1';
$db   = 'candycutz_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $stmt = $pdo->query("
        SELECT p.id as payment_id, p.status as payment_status, p.receipt_image, a.id as appointment_id, a.barber_id 
        FROM payments p 
        JOIN appointments a ON p.appointment_id = a.id
        WHERE p.status = 'awaiting_verification' OR p.receipt_image IS NOT NULL
    ");
    $payments = $stmt->fetchAll();

    echo json_encode($payments, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
