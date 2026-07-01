<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=candycutz_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare("UPDATE working_hours SET is_available = 0 WHERE barber_id = 1 AND day_of_week = 1");
    $stmt->execute();
    echo "Updated!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
