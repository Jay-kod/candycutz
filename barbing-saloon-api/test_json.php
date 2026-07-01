<?php
$data = '{"working_hours":[{"day_of_week":1,"open_time":"09:00","close_time":"17:00","is_closed":true}]}';
$decoded = json_decode($data, true);
$hour = $decoded['working_hours'][0];
$is_available = empty($hour['is_closed']) ? 1 : 0;
echo "is_closed: " . ($hour['is_closed'] ? 'true' : 'false') . "\n";
echo "is_available: $is_available\n";
