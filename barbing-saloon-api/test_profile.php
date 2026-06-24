<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
$stmt = $pdo->query('DESCRIBE appointments');
foreach ($stmt->fetchAll() as $row) {
    echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . "\n";
}
?>
