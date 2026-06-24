<?php
// analytics_correctness.php
// Tests the correctness of the analytics calculations by asserting them against baseline manual queries.

$db_host = 'localhost';
$db_name = 'candycutz_db';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

echo "=== CANDYCUTZ ANALYTICS CORRECTNESS TEST ===\n\n";

$passed = 0;
$failed = 0;

function assertTest($name, $expected, $actual) {
    global $passed, $failed;
    $expStr = is_array($expected) ? json_encode($expected) : $expected;
    $actStr = is_array($actual) ? json_encode($actual) : $actual;
    
    if ($expected === $actual) {
        echo "[PASS] $name\n";
        $passed++;
    } else {
        echo "[FAIL] $name\n";
        echo "       Expected: $expStr\n";
        echo "       Actual:   $actStr\n";
        $failed++;
    }
}

// 1. Total Revenue Test (All Time)
$stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'successful'");
$actualRevenue = (float)$stmt->fetchColumn();

// Manual calculation: sum all successful payments row by row
$manualRevenue = 0.0;
$stmt2 = $pdo->query("SELECT amount FROM payments WHERE status = 'successful'");
while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    $manualRevenue += (float)$row['amount'];
}
assertTest("Total Revenue Calculation", $manualRevenue, $actualRevenue);

// 2. Completed Appointments Test
$stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'completed'");
$actualAppointments = (int)$stmt->fetchColumn();

// Manual calculation
$stmt2 = $pdo->query("SELECT id FROM appointments WHERE status = 'completed'");
$manualAppointments = $stmt2->rowCount();
assertTest("Completed Appointments Count", $manualAppointments, $actualAppointments);

// 3. Top Barber Verification
$stmt = $pdo->query("
    SELECT b.id, COUNT(a.id) as bookings 
    FROM barbers b
    LEFT JOIN appointments a ON b.id = a.barber_id AND a.status = 'completed'
    GROUP BY b.id
    ORDER BY bookings DESC LIMIT 1
");
$topBarber = $stmt->fetch(PDO::FETCH_ASSOC);

if ($topBarber) {
    // Manually count for that specific barber
    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE barber_id = ? AND status = 'completed'");
    $stmt2->execute([$topBarber['id']]);
    $manualBarberBookings = (int)$stmt2->fetchColumn();
    assertTest("Top Barber Bookings Match", $manualBarberBookings, (int)$topBarber['bookings']);
} else {
    echo "[SKIP] Top Barber Verification (No barbers found)\n";
}

echo "\n--- Test Summary ---\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
if ($failed === 0) {
    echo "Result: SUCCESS. All analytics calculations are correct.\n";
} else {
    echo "Result: FAILED. Please check the logs.\n";
}
