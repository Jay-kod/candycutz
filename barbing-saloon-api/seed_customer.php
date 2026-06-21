<?php
$pdo = new PDO('mysql:host=localhost;dbname=barbing_saloon', 'root', '');

// Check if customer already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute(['customer@candycutz.com']);
$existing = $stmt->fetch();

if ($existing) {
    echo "Customer user already exists with ID: " . $existing['id'] . "\n";
    // Update the password just in case
    $hash = password_hash('customer123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'customer' WHERE email = ?");
    $stmt->execute([$hash, 'customer@candycutz.com']);
    echo "Password updated.\n";
} else {
    $hash = password_hash('customer123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute(['Demo Customer', 'customer@candycutz.com', $hash, 'customer', '555-0099']);
    echo "Customer user created with ID: " . $pdo->lastInsertId() . "\n";
}

echo "Done! Login with: customer@candycutz.com / customer123\n";
