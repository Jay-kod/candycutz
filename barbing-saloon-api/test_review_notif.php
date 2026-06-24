<?php
// Test: submit a review and check notifications
$ch = curl_init('http://localhost:8000/customer/testimonials');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer tok_customer_3',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'barber_id' => 1,
    'service_id' => 1,
    'rating' => 5,
    'comment' => 'Great haircut notification test'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);
echo "Review submission: " . $res . "\n\n";

// Check admin notifications (user 1 is admin)
$ch = curl_init('http://localhost:8000/notifications');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer tok_admin_1'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);
$data = json_decode($res, true);
echo "Admin notifications (latest 3):\n";
$notifs = array_slice($data['data'] ?? [], 0, 3);
foreach ($notifs as $n) {
    echo "  - [{$n['type']}] {$n['title']}: {$n['message']}\n";
}

echo "\n";

// Check customer notifications (user 3)
$ch = curl_init('http://localhost:8000/notifications');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer tok_customer_3'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);
$data = json_decode($res, true);
echo "Customer notifications (latest 3):\n";
$notifs = array_slice($data['data'] ?? [], 0, 3);
foreach ($notifs as $n) {
    echo "  - [{$n['type']}] {$n['title']}: {$n['message']}\n";
}
