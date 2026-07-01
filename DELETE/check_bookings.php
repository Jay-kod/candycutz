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

    // Get a barber_id
    $stmt = $pdo->query("SELECT id, user_id FROM barbers LIMIT 1");
    $barber = $stmt->fetch();
    echo "Barber ID: " . $barber['id'] . ", User ID: " . $barber['user_id'] . "\n\n";

    // Run the same query the API uses for bookings
    $stmt = $pdo->prepare("
        SELECT a.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
               s.name as service_name, s.price, s.duration_minutes,
               p.status as payment_status, p.receipt_image, p.transaction_ref, p.verified_by
        FROM appointments a
        INNER JOIN users u ON a.customer_id = u.id
        INNER JOIN services s ON a.service_id = s.id
        LEFT JOIN payments p ON a.id = p.appointment_id
        WHERE a.barber_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->execute([$barber['id']]);
    $bookings = $stmt->fetchAll();

    echo "Total Bookings: " . count($bookings) . "\n\n";
    foreach ($bookings as $b) {
        echo "ID: {$b['id']}, Customer: {$b['customer_name']}, Payment Status: " . ($b['payment_status'] ?? 'NULL') . 
             ", Receipt: " . ($b['receipt_image'] ?? 'NULL') . 
             ", Verified By: " . ($b['verified_by'] ?? 'NULL') . "\n";
    }
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
