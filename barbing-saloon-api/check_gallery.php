<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
$stmt = $pdo->query("SELECT id, title, image_path, image_url, category FROM gallery");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
