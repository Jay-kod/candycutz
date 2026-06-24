<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '');
$stmt = $pdo->query('DESCRIBE services');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
