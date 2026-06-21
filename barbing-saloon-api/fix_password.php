<?php
$host = '127.0.0.1';
$db = 'barbing_saloon';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $password = password_hash('barber123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$password, 'barber@candycutz.com']);
    
    echo "Password updated successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
