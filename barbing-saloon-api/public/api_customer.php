<?php
// Customer API Routes

// Authentication Check
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);
$parts = explode('_', $token);
$userId = isset($parts[2]) ? intval($parts[2]) : 0;

if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthenticated']);
    exit;
}

// Ensure user is customer
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userRole = $stmt->fetchColumn();

// Wishlist Endpoints
if ($path === '/customer/wishlist') {
    if ($method === 'GET') {
        $stmt = $pdo->prepare("
            SELECT w.id, w.item_type, w.item_id, w.created_at,
                s.name as service_name, s.price, s.image as service_image,
                g.image_url as gallery_image, g.title as gallery_title
            FROM wishlists w 
            LEFT JOIN services s ON w.item_type = 'service' AND w.item_id = s.id 
            LEFT JOIN gallery g ON w.item_type = 'gallery' AND w.item_id = g.id 
            WHERE w.customer_id = ? ORDER BY w.created_at DESC
        ");
        $stmt->execute([$userId]);
        echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }
    
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $item_type = $data['item_type'] ?? '';
        $item_id = $data['item_id'] ?? 0;
        
        if (!in_array($item_type, ['service', 'gallery']) || !$item_id) {
            http_response_code(400); echo json_encode(['error' => 'Invalid data']); exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO wishlists (customer_id, item_type, item_id) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $item_type, $item_id]);
            echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
        } catch (PDOException $e) {
            // Might be duplicate key, just ignore
            echo json_encode(['success' => true, 'message' => 'Already in wishlist']);
        }
        exit;
    }
}

// Delete wishlist item
if ($method === 'DELETE' && preg_match('/^\/customer\/wishlist\/(\d+)$/', $path, $matches)) {
    $id = $matches[1];
    $stmt = $pdo->prepare("DELETE FROM wishlists WHERE id = ? AND customer_id = ?");
    $stmt->execute([$id, $userId]);
    echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
    exit;
}

// Testimonials (Write Review)
if ($method === 'POST' && $path === '/customer/testimonials') {
    $data = json_decode(file_get_contents('php://input'), true);
    $barber_id = $data['barber_id'] ?? null;
    $rating = $data['rating'] ?? 5;
    $comment = $data['comment'] ?? '';
    
    if (!$comment || $rating < 1 || $rating > 5) {
        http_response_code(400); echo json_encode(['error' => 'Invalid rating or comment']); exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO testimonials (customer_id, barber_id, rating, comment, is_approved) VALUES (?, ?, ?, ?, 0)");
    $stmt->execute([$userId, $barber_id, $rating, $comment]);
    echo json_encode(['success' => true, 'message' => 'Testimony submitted for review']);
    exit;
}

// Checkout (Mock Payment)
if ($method === 'POST' && $path === '/customer/checkout') {
    $data = json_decode(file_get_contents('php://input'), true);
    $appointment_id = $data['appointment_id'] ?? 0;
    $amount = $data['amount'] ?? 0;
    $payment_method = $data['payment_method'] ?? 'mock_card';
    
    if (!$appointment_id || !$amount) {
        http_response_code(400); echo json_encode(['error' => 'Invalid payment data']); exit;
    }
    
    // Simulate payment processing
    $transaction_ref = 'TXN_' . strtoupper(uniqid());
    
    $stmt = $pdo->prepare("INSERT INTO payments (customer_id, appointment_id, amount, status, payment_method, transaction_ref) VALUES (?, ?, ?, 'successful', ?, ?)");
    $stmt->execute([$userId, $appointment_id, $amount, $payment_method, $transaction_ref]);
    
    // Update appointment status to confirmed if it was pending
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'confirmed' WHERE id = ? AND customer_id = ? AND status = 'pending'");
    $stmt->execute([$appointment_id, $userId]);
    
    echo json_encode(['success' => true, 'transaction_ref' => $transaction_ref, 'message' => 'Payment successful']);
    exit;
}
