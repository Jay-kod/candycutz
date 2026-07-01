<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=candycutz_db;charset=utf8mb4', 'root', '');
    $barberId = 1;
    $stmt = $pdo->prepare("SELECT id, day_of_week, start_time as open_time, end_time as close_time, is_available as is_closed FROM working_hours WHERE barber_id = ? ORDER BY day_of_week ASC");
    $stmt->execute([$barberId]);
    $hours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($hours as &$hour) {
        $hour['is_closed'] = !$hour['is_closed']; // convert is_available to is_closed boolean
        $hour['open_time'] = substr($hour['open_time'], 0, 5); // '09:00:00' to '09:00'
        $hour['close_time'] = substr($hour['close_time'], 0, 5);
    }
    
    print_r($hours);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
