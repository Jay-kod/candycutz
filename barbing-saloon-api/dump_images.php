<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
$stmt = $pdo->query('SELECT u.name, u.avatar, b.profile_image FROM users u JOIN barbers b ON u.id = b.user_id');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
