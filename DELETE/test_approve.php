<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
// Get a barber id
$barber = $pdo->query("SELECT * FROM barbers LIMIT 1")->fetch();
$barberId = $barber['id'];
$userId = $barber['user_id'];
// Get a pending appointment for this barber
$appt = $pdo->prepare("SELECT a.id, a.customer_id FROM appointments a JOIN payments p ON a.id = p.appointment_id WHERE a.barber_id = ? AND p.status = 'awaiting_verification' LIMIT 1");
$appt->execute([$barberId]);
$appointment = $appt->fetch();

if (!$appointment) {
    echo "No appointment found\n";
    exit;
}

$appointmentId = $appointment['id'];

try {
    $pdo->prepare("UPDATE payments SET status = 'successful', verified_by = 'barber' WHERE appointment_id = ?")->execute([$appointmentId]);
    $pdo->prepare("UPDATE appointments SET status = 'confirmed' WHERE id = ?")->execute([$appointmentId]);
    
    // Notify customer
    $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message, related_entity_id) VALUES (?, 'customer', ?, 'payment', 'Payment Verified', 'Your payment has been verified and your booking is confirmed.', ?)");
    $stmt->execute([$userId, $appointment['customer_id'], $appointmentId]);
    
    // Notify admin
    $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, type, title, message, related_entity_id) VALUES (?, 'admin', 'payment', 'Payment Verified by Barber', 'Barber has verified the payment and confirmed booking.', ?)");
    $stmt->execute([$userId, $appointmentId]);
    
    // Audit Log
    $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'verify_payment', 'payment', ?, ?)")->execute([$userId, $appointmentId, '127.0.0.1']);
    
    echo "Success!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
