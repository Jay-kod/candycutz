<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '');
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute(['customer@candycutz.com']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "User exists: " . ($user ? 'Yes' : 'No') . "\n";
if ($user) {
    echo "Hash in DB: " . $user['password'] . "\n";
    echo "Password verify: " . (password_verify('customer123', $user['password']) ? 'True' : 'False') . "\n";
}
