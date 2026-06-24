<?php
$host = '127.0.0.1';
$db   = 'candycutz_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // 1. Appointments Today
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ?");
    $stmt->execute([$today]);
    $appointmentsToday = $stmt->fetchColumn();

    // 2. Pending Appointments
    $stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'");
    $pendingAppointments = $stmt->fetchColumn();

    // 3. Services
    $stmt = $pdo->query("SELECT COUNT(*) FROM services");
    $services = $stmt->fetchColumn();

    // 4. Barbers
    $stmt = $pdo->query("SELECT COUNT(*) FROM barbers");
    $barbers = $stmt->fetchColumn();

    // 5. Revenue Estimate (completed appointments sum)
    $stmt = $pdo->query("SELECT SUM(s.price) FROM appointments a JOIN services s ON a.service_id = s.id WHERE a.status = 'completed'");
    $revenueEstimate = $stmt->fetchColumn() ?: 0;

    echo json_encode([
        'data' => [
            'stats' => [
                'appointments_today' => (int)$appointmentsToday,
                'pending_appointments' => (int)$pendingAppointments,
                'services' => (int)$services,
                'barbers' => (int)$barbers
            ],
            'reports' => [
                'revenue_estimate' => (float)$revenueEstimate
            ]
        ]
    ]);
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
