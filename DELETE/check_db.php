<?php
// Test the generateVerificationCode function
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function generateVerificationCode($pdo, $barberId) {
    $stmt = $pdo->prepare("SELECT u.name FROM barbers b JOIN users u ON b.user_id = u.id WHERE b.id = ?");
    $stmt->execute([$barberId]);
    $bName = $stmt->fetchColumn() ?: 'BA';
    $initials = '';
    foreach (explode(' ', trim($bName)) as $w) { if(!empty($w)) $initials .= strtoupper($w[0]); }
    $initials = substr($initials ?: 'BA', 0, 2);
    return $initials . '-' . date('dmy') . '-' . sprintf('%04d', mt_rand(1000, 9999));
}

// Get all barbers
$stmt = $pdo->query("SELECT id FROM barbers");
$barbers = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Found barbers: " . implode(', ', $barbers) . "\n";

foreach ($barbers as $bid) {
    $code = generateVerificationCode($pdo, $bid);
    echo "Barber $bid => Code: $code\n";
}

// Also test what happens when verifying a receipt
echo "\n--- Checking pending appointments with awaiting_verification ---\n";
$stmt = $pdo->query("SELECT a.id, a.status, a.barber_id, p.status as payment_status 
                      FROM appointments a 
                      LEFT JOIN payments p ON a.id = p.appointment_id 
                      WHERE p.status = 'pending' OR p.status = 'awaiting_verification'
                      LIMIT 5");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
