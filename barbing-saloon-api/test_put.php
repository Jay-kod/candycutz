<?php
$data = [
    'working_hours' => [
        [
            'day_of_week' => 1,
            'open_time' => '09:00',
            'close_time' => '17:00',
            'is_closed' => true
        ]
    ]
];

$ch = curl_init('http://localhost:8000/barber/schedule');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    // I need a valid barber JWT token... I don't have one easily available here.
]);

$response = curl_exec($ch);
curl_close($ch);
echo "Response: $response\n";
