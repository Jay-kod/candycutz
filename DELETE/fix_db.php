<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '');
// Get first barber
$stmt = $pdo->query('SELECT id FROM barbers LIMIT 1');
$barber = $stmt->fetch();
if ($barber) {
    $pdo->query('UPDATE services SET barber_id = ' . $barber['id'] . ' WHERE barber_id IS NULL');
    echo "Updated services to barber_id = " . $barber['id'];
}
