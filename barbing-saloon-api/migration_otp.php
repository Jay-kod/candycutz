<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

try {
    $pdo->exec("ALTER TABLE appointments ADD COLUMN verification_code VARCHAR(10) NULL DEFAULT NULL AFTER status");
    echo "Successfully added verification_code column to appointments table.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
