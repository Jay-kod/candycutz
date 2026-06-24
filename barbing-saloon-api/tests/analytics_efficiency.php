<?php
// analytics_efficiency.php
// Tests the load time and query efficiency of the Admin and Barber Analytics endpoints.

require_once __DIR__ . '/../vendor/autoload.php';

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

function measureQueryTime($pdo, $sql, $params = []) {
    $start = microtime(true);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $stmt->fetchAll();
    $end = microtime(true);
    return ($end - $start) * 1000; // ms
}

echo "=== CANDYCUTZ ANALYTICS EFFICIENCY TEST ===\n";
echo "Benchmarking SQL Query execution times...\n\n";

$ranges = ['7d' => '7 DAY', '30d' => '30 DAY', 'month' => '1 MONTH'];

foreach ($ranges as $label => $interval) {
    echo "--- Range: $label ---\n";
    $df = "AND created_at >= DATE_SUB(NOW(), INTERVAL $interval)";
    $dfA = "AND a.created_at >= DATE_SUB(NOW(), INTERVAL $interval)";

    $totalTime = 0;

    // 1. Business Stats (Revenue)
    $t1 = measureQueryTime($pdo, "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'successful' $df");
    echo "1. Total Revenue Query: " . number_format($t1, 2) . " ms\n";
    $totalTime += $t1;

    // 2. Business Stats (Appointments)
    $t2 = measureQueryTime($pdo, "SELECT COUNT(*) FROM appointments WHERE status = 'completed' $df");
    echo "2. Total Appointments Query: " . number_format($t2, 2) . " ms\n";
    $totalTime += $t2;

    // 3. Top Barbers (Complex JOIN)
    $t3 = measureQueryTime($pdo, "
        SELECT b.id, u.name, b.rating, COUNT(a.id) as bookings, COALESCE(SUM(p.amount), 0) as revenue
        FROM barbers b
        JOIN users u ON b.user_id = u.id
        LEFT JOIN appointments a ON b.id = a.barber_id AND a.status = 'completed' $dfA
        LEFT JOIN payments p ON p.appointment_id = a.id AND p.status = 'successful'
        GROUP BY b.id
        ORDER BY revenue DESC, bookings DESC
        LIMIT 5
    ");
    echo "3. Top Barbers Complex Query: " . number_format($t3, 2) . " ms\n";
    $totalTime += $t3;

    // 4. Revenue Trends (Group By Month)
    $t4 = measureQueryTime($pdo, "
        SELECT DATE_FORMAT(a.appointment_date, '%b') as month, COALESCE(SUM(p.amount), 0) as revenue 
        FROM appointments a
        LEFT JOIN payments p ON p.appointment_id = a.id AND p.status = 'successful'
        WHERE a.appointment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH) AND a.status = 'completed'
        GROUP BY YEAR(a.appointment_date), MONTH(a.appointment_date), month
        ORDER BY YEAR(a.appointment_date) ASC, MONTH(a.appointment_date) ASC
    ");
    echo "4. Revenue Trends (6mo) Query: " . number_format($t4, 2) . " ms\n";
    $totalTime += $t4;

    echo "Total Database Load Time for $label: " . number_format($totalTime, 2) . " ms\n\n";
}

echo "=== EFFICIENCY REPORT ===\n";
echo "If total DB load time is < 50ms, the queries are highly efficient.\n";
echo "For even faster load times, the frontend now implements localStorage caching,\n";
echo "bringing the perceived load time to 0ms for return visits.\n";
