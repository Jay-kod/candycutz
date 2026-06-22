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

if ($userRole !== 'customer' && $userRole !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized: Customer access required']);
    exit;
}

// ==========================================
// NOTIFICATIONS
// ==========================================
if ($method === 'GET' && $path === '/customer/notifications') {
    // Get unread count and latest notifications
    // Simple implementation for now: fetch all 'all_customers' notifications
    $stmt = $pdo->prepare("SELECT n.*, u.name as sender_name, u.avatar as sender_avatar 
                           FROM notifications n
                           LEFT JOIN users u ON n.sender_id = u.id
                           WHERE n.recipient_type = 'all_customers' OR (n.recipient_type = 'customer' AND n.recipient_id = ?)
                           ORDER BY n.created_at DESC LIMIT 20");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['data' => $notifications]);
    exit;
}

// ==========================================
// CUSTOMER DASHBOARD
// ==========================================
if ($method === 'GET' && $path === '/customer/dashboard') {
    $stats = [];
    
    // Total Bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE customer_id = ?");
    $stmt->execute([$userId]);
    $stats['total_bookings'] = (int)$stmt->fetchColumn();
    
    // Upcoming Bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE customer_id = ? AND status IN ('pending', 'confirmed') AND appointment_date >= CURRENT_DATE");
    $stmt->execute([$userId]);
    $stats['upcoming_bookings'] = (int)$stmt->fetchColumn();
    
    // Completed Bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE customer_id = ? AND status = 'completed'");
    $stmt->execute([$userId]);
    $stats['completed_bookings'] = (int)$stmt->fetchColumn();
    
    // Reviews Count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM testimonials WHERE customer_id = ?");
    $stmt->execute([$userId]);
    $stats['reviews_count'] = (int)$stmt->fetchColumn();
    
    // Upcoming Appointments
    $stmt = $pdo->prepare("SELECT a.id, a.appointment_date, a.appointment_time, a.status, s.name as service_name, b.name as barber_name 
                           FROM appointments a 
                           LEFT JOIN services s ON a.service_id = s.id 
                           LEFT JOIN barbers b_tbl ON a.barber_id = b_tbl.id
                           LEFT JOIN users b ON b_tbl.user_id = b.id
                           WHERE a.customer_id = ? AND a.status IN ('pending', 'confirmed') AND a.appointment_date >= CURRENT_DATE
                           ORDER BY a.appointment_date ASC, a.appointment_time ASC LIMIT 5");
    $stmt->execute([$userId]);
    $upcoming_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format for frontend
    foreach ($upcoming_appointments as &$appt) {
        $appt['service'] = ['name' => $appt['service_name']];
        $appt['barber'] = ['name' => $appt['barber_name']];
        unset($appt['service_name'], $appt['barber_name']);
    }
    
    echo json_encode(['data' => [
        'stats' => $stats,
        'upcoming_appointments' => $upcoming_appointments
    ]]);
    exit;
}

// ==========================================
// CUSTOMER BOOKINGS
// ==========================================

// GET all bookings
if ($method === 'GET' && $path === '/customer/bookings') {
    $stmt = $pdo->prepare("SELECT a.*, s.name as service_name, s.price as service_price, b.name as barber_name 
                           FROM appointments a 
                           LEFT JOIN services s ON a.service_id = s.id 
                           LEFT JOIN barbers b_tbl ON a.barber_id = b_tbl.id
                           LEFT JOIN users b ON b_tbl.user_id = b.id
                           WHERE a.customer_id = ? 
                           ORDER BY a.appointment_date DESC, a.appointment_time DESC");
    $stmt->execute([$userId]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format for frontend
    foreach ($bookings as &$booking) {
        $booking['service'] = [
            'name' => $booking['service_name'],
            'price' => $booking['service_price']
        ];
        $booking['barber'] = ['name' => $booking['barber_name']];
        unset($booking['service_name'], $booking['service_price'], $booking['barber_name']);
    }
    
    echo json_encode(['data' => $bookings]);
    exit;
}

// POST create booking
if ($method === 'POST' && $path === '/customer/bookings') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $barber_id = $data['barber_id'] ?? null;
    $service_id = $data['service_id'] ?? null;
    $date = $data['appointment_date'] ?? null;
    $time = $data['appointment_time'] ?? null;
    $notes = $data['notes'] ?? '';
    
    if (!$barber_id || !$service_id || !$date || !$time) {
        http_response_code(400); echo json_encode(['error' => 'Missing required fields']); exit;
    }
    
    // Simple check if slot is already taken
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE barber_id = ? AND appointment_date = ? AND appointment_time = ? AND status IN ('pending', 'confirmed')");
    $stmt->execute([$barber_id, $date, $time]);
    if ($stmt->fetchColumn() > 0) {
        http_response_code(409); echo json_encode(['error' => 'Time slot is no longer available']); exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO appointments (customer_id, barber_id, service_id, appointment_date, appointment_time, status, notes) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
    $stmt->execute([$userId, $barber_id, $service_id, $date, $time, $notes]);
    
    echo json_encode(['success' => true, 'message' => 'Booking created successfully']);
    exit;
}

// PATCH cancel booking
if ($method === 'PATCH' && preg_match('/^\/customer\/bookings\/(\d+)\/cancel$/', $path, $matches)) {
    $bookingId = $matches[1];
    
    // Ensure the booking belongs to the customer and is cancellable
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND customer_id = ? AND status IN ('pending', 'confirmed')");
    $stmt->execute([$bookingId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Booking cancelled']);
    } else {
        http_response_code(400); echo json_encode(['error' => 'Booking cannot be cancelled or not found']);
    }
    exit;
}

// Wishlist Endpoints
if ($path === '/customer/wishlist') {
    if ($method === 'GET') {
        $stmt = $pdo->prepare("
            SELECT w.id, w.item_type, w.item_id, w.created_at,
                s.name as service_name, s.price, s.image as service_image,
                g.image_path as gallery_image, g.title as gallery_title
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

// Delete wishlist item by DB ID
if ($method === 'DELETE' && preg_match('/^\/customer\/wishlist\/(\d+)$/', $path, $matches)) {
    $id = $matches[1];
    $stmt = $pdo->prepare("DELETE FROM wishlists WHERE id = ? AND customer_id = ?");
    $stmt->execute([$id, $userId]);
    echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
    exit;
}

// Delete wishlist item by type and item_id
if ($method === 'DELETE' && $path === '/customer/wishlist/item') {
    $item_type = $_GET['type'] ?? '';
    $item_id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM wishlists WHERE item_type = ? AND item_id = ? AND customer_id = ?");
    $stmt->execute([$item_type, $item_id, $userId]);
    echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
    exit;
}

// Blog Reactions
if ($method === 'POST' && preg_match('/^\/customer\/blog\/(\d+)\/react$/', $path, $matches)) {
    $postId = $matches[1];
    $data = json_decode(file_get_contents('php://input'), true);
    $type = $data['type'] ?? '';
    
    if (!in_array($type, ['love', 'dislike'])) {
        http_response_code(400); echo json_encode(['error' => 'Invalid reaction type']); exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO blog_reactions (post_id, customer_id, reaction_type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE reaction_type = ?");
        $stmt->execute([$postId, $userId, $type, $type]);
        
        $counts = $pdo->prepare("SELECT SUM(CASE WHEN reaction_type = 'love' THEN 1 ELSE 0 END) as loves_count, SUM(CASE WHEN reaction_type = 'dislike' THEN 1 ELSE 0 END) as dislikes_count FROM blog_reactions WHERE post_id = ?");
        $counts->execute([$postId]);
        $result = $counts->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'loves_count' => (int)$result['loves_count'],
            'dislikes_count' => (int)$result['dislikes_count'],
            'user_reaction' => $type
        ]);
    } catch (PDOException $e) {
        http_response_code(500); echo json_encode(['error' => 'Database error']);
    }
    exit;
}

if ($method === 'DELETE' && preg_match('/^\/customer\/blog\/(\d+)\/react$/', $path, $matches)) {
    $postId = $matches[1];
    $stmt = $pdo->prepare("DELETE FROM blog_reactions WHERE post_id = ? AND customer_id = ?");
    $stmt->execute([$postId, $userId]);
    
    $counts = $pdo->prepare("SELECT SUM(CASE WHEN reaction_type = 'love' THEN 1 ELSE 0 END) as loves_count, SUM(CASE WHEN reaction_type = 'dislike' THEN 1 ELSE 0 END) as dislikes_count FROM blog_reactions WHERE post_id = ?");
    $counts->execute([$postId]);
    $result = $counts->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'loves_count' => (int)$result['loves_count'],
        'dislikes_count' => (int)$result['dislikes_count'],
        'user_reaction' => null
    ]);
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
