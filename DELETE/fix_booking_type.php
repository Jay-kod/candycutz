<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Reset all to online
$pdo->exec("UPDATE appointments SET booking_type = 'online'");

// 2. Identify walk-ins
// A walk-in is an appointment that does not have a verification code, BUT is either confirmed or completed.
// (Online bookings always get a verification code when they become confirmed).
// Also, pending appointments are ALWAYS online.
$updated = $pdo->exec("UPDATE appointments SET booking_type = 'walk_in' WHERE (status = 'confirmed' OR status = 'completed') AND (verification_code IS NULL OR verification_code = '')");

echo "Fixed booking types. $updated were marked as walk_ins.\n";

// Let's verify
$stmt = $pdo->query("SELECT id, status, verification_code, booking_type FROM appointments ORDER BY id DESC LIMIT 20");
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($appointments as $appt) {
    echo "ID: {$appt['id']} | Status: {$appt['status']} | Type: {$appt['booking_type']} | Code: {$appt['verification_code']}\n";
}
