<?php
$host = '127.0.0.1';
$db = 'barbing_saloon';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL,
        phone VARCHAR(20) NULL,
        avatar VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Insert Admin
    $password = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Super Admin', 'superadmin@salon.com', $password, 'super_admin', '08030000001']);
    
    // Insert Barber
    $stmt->execute(['Barber 1', 'barber1@salon.com', $password, 'barber', '08030000003']);
    
    echo "Seed successful!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
