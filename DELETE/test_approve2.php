<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

try {
    $userId = 1;
    $appointmentId = 1;
    $customerId = 2;

    $pdo->prepare("UPDATE payments SET status = 'successful', verified_by = 'barber' WHERE appointment_id = ?")->execute([$appointmentId]);
    $pdo->prepare("UPDATE appointments SET status = 'confirmed' WHERE id = ?")->execute([$appointmentId]);
    
    // Notify customer
    $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message, related_entity_id) VALUES (?, 'customer', ?, 'payment', 'Payment Verified', 'Your payment has been verified and your booking is confirmed.', ?)");
    $stmt->execute([$userId, $customerId, $appointmentId]);
    
    // Notify admin
    $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, type, title, message, related_entity_id) VALUES (?, 'admin', 'payment', 'Payment Verified by Barber', 'Barber has verified the payment and confirmed booking.', ?)");
    $stmt->execute([$userId, $appointmentId]);
    
    // Audit Log
    $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'verify_payment', 'payment', ?, ?)")->execute([$userId, $appointmentId, '127.0.0.1']);
    
    echo "Success!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
