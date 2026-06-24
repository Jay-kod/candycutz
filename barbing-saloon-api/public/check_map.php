<?php
$pdo = new PDO('mysql:host=localhost;dbname=barbing_saloon', 'root', '');
$stmt = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` LIKE 'contact_map%'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);
