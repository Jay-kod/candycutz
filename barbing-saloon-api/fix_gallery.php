<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
$stmt = $pdo->prepare("DELETE FROM gallery WHERE image_path LIKE '/images/gallery/%'");
$stmt->execute();
echo "Deleted " . $stmt->rowCount() . " dummy gallery images.\n";
