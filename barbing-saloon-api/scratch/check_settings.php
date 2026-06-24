<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '');
$stmt = $pdo->query("SELECT `key`, `value` FROM settings");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['key'] . ' => ' . $row['value'] . PHP_EOL;
}
