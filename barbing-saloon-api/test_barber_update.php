<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=candycutz_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $working_hours = [
        [
            'day_of_week' => 1,
            'open_time' => '09:00',
            'close_time' => '17:00',
            'is_closed' => true
        ]
    ];
    $barberId = 1;

    $pdo->beginTransaction();
    foreach ($working_hours as $hour) {
        $is_available = empty($hour['is_closed']) ? 1 : 0;
        $start = $hour['open_time'] ?? '09:00';
        $end = $hour['close_time'] ?? '17:00';
        $day = $hour['day_of_week'];
        
        $stmt = $pdo->prepare("SELECT id FROM working_hours WHERE barber_id = ? AND day_of_week = ?");
        $stmt->execute([$barberId, $day]);
        if ($stmt->fetchColumn()) {
            $pdo->prepare("UPDATE working_hours SET start_time = ?, end_time = ?, is_available = ? WHERE barber_id = ? AND day_of_week = ?")
                ->execute([$start, $end, $is_available, $barberId, $day]);
        } else {
            $pdo->prepare("INSERT INTO working_hours (barber_id, day_of_week, start_time, end_time, is_available) VALUES (?, ?, ?, ?, ?)")
                ->execute([$barberId, $day, $start, $end, $is_available]);
        }
    }
    $pdo->commit();
    echo "Done!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
