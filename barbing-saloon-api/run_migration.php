<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = file_get_contents(__DIR__ . '/database/migrations/add_payment_receipt_notifications.sql');
$pdo->exec($sql);
echo "Migration applied successfully!";
