<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $pdo->exec("ALTER TABLE appointments ADD COLUMN booking_type VARCHAR(20) DEFAULT 'online' AFTER status");
    echo "Column 'booking_type' added.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column 'booking_type' already exists.\n";
    } else {
        throw $e;
    }
}

// Mark existing walk-ins (no verification code = walk-in)
$stmt = $pdo->exec("UPDATE appointments SET booking_type = 'walk_in' WHERE verification_code IS NULL OR verification_code = ''");
echo "Updated $stmt existing walk-in rows.\n";
echo "Done.\n";
