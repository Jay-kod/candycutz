<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=candycutz_db;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// First, list all users
$stmt = $pdo->query("SELECT id, name, email, role FROM users ORDER BY id");
$users = $stmt->fetchAll();

echo "=== All Users in Database ===\n";
foreach ($users as $u) {
    echo "ID: {$u['id']} | Name: {$u['name']} | Email: {$u['email']} | Role: {$u['role']}\n";
}

// Generate a fresh bcrypt hash for 'password'
$plainPassword = 'password';
$hash = password_hash($plainPassword, PASSWORD_BCRYPT);

echo "\n=== Resetting ALL user passwords to 'password' ===\n";
echo "New hash: $hash\n";

// Verify hash works before updating
$verify = password_verify($plainPassword, $hash);
echo "Pre-update verification: " . ($verify ? 'PASS' : 'FAIL') . "\n";

if (!$verify) {
    echo "ERROR: Hash generation failed. Aborting.\n";
    exit(1);
}

// Update all users
$stmt = $pdo->prepare("UPDATE users SET password = ?");
$stmt->execute([$hash]);
echo "Updated " . $stmt->rowCount() . " users.\n";

// Verify each user
echo "\n=== Verifying passwords for all users ===\n";
$stmt = $pdo->query("SELECT id, email, password FROM users ORDER BY id");
$users = $stmt->fetchAll();

$allPassed = true;
foreach ($users as $u) {
    $ok = password_verify($plainPassword, $u['password']);
    $status = $ok ? '✓ PASS' : '✗ FAIL';
    echo "{$u['email']}: $status\n";
    if (!$ok) $allPassed = false;
}

echo "\n" . ($allPassed ? "ALL PASSWORDS VERIFIED SUCCESSFULLY!" : "SOME PASSWORDS FAILED VERIFICATION!") . "\n";
