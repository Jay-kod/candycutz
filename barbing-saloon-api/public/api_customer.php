<?php
// Customer API Routes

// Authentication Check
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthenticated']);
    exit;
}

try {
    $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($jwtSecret, 'HS256'));
    $userId = $decoded->sub;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
}

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
// CUSTOMER PROFILE
// ==========================================
if ($path === '/customer/profile') {
    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT id, name, username, email, phone, avatar, notification_preferences, last_username_change, last_email_change, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT a.id, a.appointment_date, a.appointment_time, a.status, s.name as service_name 
            FROM appointments a 
            LEFT JOIN services s ON a.service_id = s.id 
            WHERE a.customer_id = ? 
            ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 5
        ");
        $stmt->execute([$userId]);
        $latest_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($latest_bookings as &$booking) {
            $booking['service'] = ['name' => $booking['service_name']];
            unset($booking['service_name']);
        }

        echo json_encode([
            'data' => [
                'profile' => $profile,
                'history' => ['latest_bookings' => $latest_bookings]
            ]
        ]);
        exit;
    }

    if ($method === 'POST') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $phone = $_POST['phone'] ?? $user['phone'];
        $username = $_POST['username'] ?? $user['username'];
        $email = $_POST['email'] ?? $user['email'];
        
        $updates = ['phone' => $phone];
        $params = ['phone' => $phone];
        
        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $filename)) {
                $updates['avatar'] = '/uploads/avatars/' . $filename;
                $params['avatar'] = $updates['avatar'];
                // Delete old avatar if exists
                if ($user['avatar'] && file_exists(__DIR__ . $user['avatar'])) {
                    @unlink(__DIR__ . $user['avatar']);
                }
            }
        }

        // Handle username change
        if ($username !== $user['username']) {
            if ($user['last_username_change'] !== null && strtotime($user['last_username_change']) > strtotime('-30 days')) {
                http_response_code(400); echo json_encode(['error' => 'Username can only be changed once every 30 days']); exit;
            }
            // Check uniqueness
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $userId]);
            if ($stmt->fetch()) {
                http_response_code(400); echo json_encode(['error' => 'Username is already taken']); exit;
            }
            $updates['username'] = $username;
            $updates['last_username_change'] = date('Y-m-d H:i:s');
            $params['username'] = $updates['username'];
            $params['last_username_change'] = $updates['last_username_change'];
        }

        // Handle email change
        if ($email !== $user['email']) {
            if (!empty($user['email']) && $user['last_email_change'] && strtotime($user['last_email_change']) > strtotime('-30 days')) {
                http_response_code(400); echo json_encode(['error' => 'Email can only be changed once every 30 days']); exit;
            }
            // Check uniqueness
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                http_response_code(400); echo json_encode(['error' => 'Email is already in use']); exit;
            }
            $updates['email'] = $email;
            $updates['last_email_change'] = date('Y-m-d H:i:s');
            $params['email'] = $updates['email'];
            $params['last_email_change'] = $updates['last_email_change'];
        }

        $params['id'] = $userId;
        $setClause = implode(', ', array_map(function($k) { return "$k = :$k"; }, array_keys($updates)));
        
        $stmt = $pdo->prepare("UPDATE users SET $setClause WHERE id = :id");
        $stmt->execute($params);

        // Check if we should notify about profile changes
        $prefs = $user['notification_preferences'] ? json_decode($user['notification_preferences'], true) : [];
        $notifySystem = $prefs['notify_system'] ?? true; // default true
        
        if ($notifySystem) {
            cc_notify_customer($pdo, (int) $userId, [
                'sender_id' => $userId,
                'type' => 'system_update',
                'title' => 'Profile Updated',
                'message' => 'Your profile information has been successfully updated.',
            ]);
            cc_notify_admin($pdo, [
                'sender_id' => $userId,
                'type' => 'system_update',
                'title' => 'Customer Profile Updated',
                'message' => 'A customer updated their profile information.',
                'related_entity_id' => (int) $userId,
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        exit;
    }
}

// ==========================================
// NOTIFICATION SETTINGS
// ==========================================
if ($method === 'POST' && $path === '/customer/notification-settings') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!is_array($data)) {
        http_response_code(400); echo json_encode(['error' => 'Invalid JSON']); exit;
    }

    $prefs = [
        'notify_bookings' => isset($data['notify_bookings']) ? (bool)$data['notify_bookings'] : true,
        'notify_system' => isset($data['notify_system']) ? (bool)$data['notify_system'] : true,
        'notify_wishlist' => isset($data['notify_wishlist']) ? (bool)$data['notify_wishlist'] : true,
        'notify_blog' => isset($data['notify_blog']) ? (bool)$data['notify_blog'] : true,
        'notify_general' => isset($data['notify_general']) ? (bool)$data['notify_general'] : true,
    ];

    $stmt = $pdo->prepare("UPDATE users SET notification_preferences = ? WHERE id = ?");
    $stmt->execute([json_encode($prefs), $userId]);

    cc_notify_customer($pdo, (int) $userId, [
        'sender_id' => $userId,
        'type' => 'general_update',
        'title' => 'Settings Updated',
        'message' => 'Your notification preferences have been successfully updated.',
    ]);

    echo json_encode(['success' => true, 'message' => 'Notification preferences updated']);
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
    $stmt = $pdo->prepare("SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.verification_code, s.name as service_name, b.name as barber_name 
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
// CUSTOMER ANALYTICS
// ==========================================
if ($method === 'GET' && $path === '/customer/analytics') {
    $range = $_GET['range'] ?? '30d';
    
    // Determine date filter based on range
    $dateFilter = "";
    if ($range === '7d') {
        $dateFilter = ">= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } else if ($range === '30d') {
        $dateFilter = ">= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    } else if ($range === 'month') {
        $dateFilter = ">= DATE_FORMAT(NOW() ,'%Y-%m-01')";
    }

    $analytics = [
        'spending_summary' => [],
        'booking_trends' => [],
        'status_breakdown' => [],
        'activity_timeline' => []
    ];

    // Spending Summary
    $df = $dateFilter ? "AND created_at $dateFilter" : "";
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total_spent, COALESCE(AVG(amount), 0) as avg_spent FROM payments WHERE customer_id = ? AND status = 'successful' $df");
    $stmt->execute([$userId]);
    $spending = $stmt->fetch(PDO::FETCH_ASSOC);
    $analytics['spending_summary']['total_spent'] = (float)$spending['total_spent'];
    $analytics['spending_summary']['avg_spent'] = (float)$spending['avg_spent'];

    // Most booked service
    $dfA = $dateFilter ? "AND a.created_at $dateFilter" : "";
    $stmt = $pdo->prepare("SELECT s.name, COUNT(*) as count FROM appointments a JOIN services s ON a.service_id = s.id WHERE a.customer_id = ? $dfA GROUP BY s.id ORDER BY count DESC LIMIT 1");
    $stmt->execute([$userId]);
    $mostBooked = $stmt->fetch(PDO::FETCH_ASSOC);
    $analytics['spending_summary']['most_booked'] = $mostBooked ? $mostBooked['name'] : 'N/A';

    // Status Breakdown
    $df = $dateFilter ? "AND created_at $dateFilter" : "";
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM appointments WHERE customer_id = ? $df GROUP BY status");
    $stmt->execute([$userId]);
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $breakdown = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0];
    foreach ($statuses as $s) {
        $breakdown[$s['status']] = (int)$s['count'];
    }
    $analytics['status_breakdown'] = $breakdown;

    // Booking Trends (last 6 months)
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(appointment_date, '%b') as month, COUNT(*) as count 
        FROM appointments 
        WHERE customer_id = ? AND appointment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        GROUP BY YEAR(appointment_date), MONTH(appointment_date), month
        ORDER BY YEAR(appointment_date) ASC, MONTH(appointment_date) ASC
    ");
    $stmt->execute([$userId]);
    $trends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $analytics['booking_trends'] = $trends;

    // Activity Timeline
    $df = $dateFilter ? "AND created_at $dateFilter" : "";
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE customer_id = ? $df");
    $stmt->execute([$userId]);
    $analytics['activity_timeline']['wishlist_items'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM testimonials WHERE customer_id = ? $df");
    $stmt->execute([$userId]);
    $analytics['activity_timeline']['reviews_given'] = (int)$stmt->fetchColumn();

    echo json_encode(['data' => $analytics]);
    exit;
}

// ==========================================
// CUSTOMER BOOKINGS
// ==========================================

// GET all bookings
if ($method === 'GET' && $path === '/customer/bookings') {
    $stmt = $pdo->prepare("SELECT a.*, s.name as service_name, s.price as service_price, b.name as barber_name, p.status as payment_status
                           FROM appointments a 
                           LEFT JOIN services s ON a.service_id = s.id 
                           LEFT JOIN barbers b_tbl ON a.barber_id = b_tbl.id
                           LEFT JOIN users b ON b_tbl.user_id = b.id
                           LEFT JOIN payments p ON a.id = p.appointment_id
                           WHERE a.customer_id = ? 
                           ORDER BY a.id DESC");
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
    
    $stmt = $pdo->prepare("INSERT INTO appointments (customer_id, barber_id, service_id, appointment_date, appointment_time, status, booking_type, notes) VALUES (?, ?, ?, ?, ?, 'pending', 'online', ?)");
    $stmt->execute([$userId, $barber_id, $service_id, $date, $time, $notes]);
    $appointment_id = $pdo->lastInsertId();
    
    // Notifications
    try {
        cc_notify_customer($pdo, (int) $userId, [
            'sender_id' => $userId,
            'type' => 'booking',
            'title' => 'Booking Created',
            'message' => 'Your booking has been created and is pending payment.',
            'related_entity_id' => (int) $appointment_id,
        ]);

        cc_notify_barber($pdo, (int) $barber_id, [
            'sender_id' => $userId,
            'type' => 'booking',
            'title' => 'New Online Booking',
            'message' => 'You have a new online booking pending payment.',
            'related_entity_id' => (int) $appointment_id,
        ]);

        cc_notify_admin($pdo, [
            'sender_id' => $userId,
            'type' => 'booking',
            'title' => 'New Online Booking',
            'message' => 'A new online booking was created via the website.',
            'related_entity_id' => (int) $appointment_id,
        ]);

        // Audit log
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'create_booking', 'appointment', ?, ?)");
        $stmt->execute([$userId, $appointment_id, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
    } catch (PDOException $e) {
        // Ignore notification errors
    }
    
    echo json_encode(['success' => true, 'message' => 'Booking created successfully', 'appointment_id' => $appointment_id]);
    exit;
}

// PATCH cancel booking
if ($method === 'PATCH' && preg_match('/^\/customer\/bookings\/(\d+)\/cancel$/', $path, $matches)) {
    $bookingId = $matches[1];
    
    // Ensure the booking belongs to the customer and is cancellable
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND customer_id = ? AND status IN ('pending', 'confirmed')");
    $stmt->execute([$bookingId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        cc_notify_appointment_parties($pdo, (int) $bookingId, [
            'sender_id' => $userId,
            'type' => 'booking',
            'customer' => [
                'title' => 'Booking Cancelled',
                'message' => 'You cancelled your booking.',
            ],
            'barber' => [
                'title' => 'Booking Cancelled',
                'message' => 'A customer cancelled their booking on your schedule.',
            ],
            'admin' => [
                'title' => 'Booking Cancelled by Customer',
                'message' => 'A customer cancelled their booking.',
            ],
        ]);
        echo json_encode(['success' => true, 'message' => 'Booking cancelled']);
    } else {
        http_response_code(400); echo json_encode(['error' => 'Booking cannot be cancelled or not found']);
    }
    exit;
}

// DELETE booking
if ($method === 'DELETE' && preg_match('/^\/customer\/bookings\/(\d+)$/', $path, $matches)) {
    $bookingId = $matches[1];
    
    // Allow deletion of any booking by the customer
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ? AND customer_id = ?");
    $stmt->execute([$bookingId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        cc_notify_appointment_parties($pdo, (int) $bookingId, [
            'sender_id' => $userId,
            'type' => 'booking',
            'customer' => [
                'title' => 'Booking Deleted',
                'message' => 'You deleted a booking from your history.',
            ],
            'barber' => [
                'title' => 'Booking Removed',
                'message' => 'A booking was removed from your schedule by the customer.',
            ],
            'admin' => [
                'title' => 'Booking Deleted by Customer',
                'message' => 'A customer deleted a booking record.',
            ],
        ]);
        echo json_encode(['success' => true, 'message' => 'Booking deleted']);
    } else {
        http_response_code(400); echo json_encode(['error' => 'Booking cannot be deleted or not found']);
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

            cc_notify_customer($pdo, (int) $userId, [
                'sender_id' => $userId,
                'type' => 'wishlist_update',
                'title' => 'Wishlist Updated',
                'message' => 'You have successfully added an item to your wishlist.',
            ]);
            cc_notify_admin($pdo, [
                'sender_id' => $userId,
                'type' => 'wishlist_update',
                'title' => 'Customer Wishlist Updated',
                'message' => 'A customer added an item to their wishlist.',
                'related_entity_id' => (int) $item_id,
            ]);

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

// Get Customer's Own Reviews
if ($method === 'GET' && $path === '/customer/reviews') {
    $stmt = $pdo->prepare("SELECT t.id, t.barber_id, t.service_id, t.rating, t.comment, t.is_approved, t.created_at,
                           u.name as barber_name, s.name as service_name
                           FROM testimonials t
                           LEFT JOIN barbers b ON t.barber_id = b.id
                           LEFT JOIN users u ON b.user_id = u.id
                           LEFT JOIN services s ON t.service_id = s.id
                           WHERE t.customer_id = ? ORDER BY t.created_at DESC");
    $stmt->execute([$userId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['data' => $reviews, 'count' => count($reviews)]);
    exit;
}

// Testimonials (Write Review)
if ($method === 'POST' && $path === '/customer/testimonials') {
    $data = json_decode(file_get_contents('php://input'), true);
    $barber_id = $data['barber_id'] ?? null;
    $service_id = $data['service_id'] ?? null;
    $rating = $data['rating'] ?? 5;
    $comment = $data['comment'] ?? '';
    
    if (!$comment || $rating < 1 || $rating > 5) {
        http_response_code(400); echo json_encode(['error' => 'Invalid rating or comment']); exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO testimonials (customer_id, barber_id, service_id, rating, comment, is_approved) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->execute([$userId, $barber_id, $service_id, $rating, $comment]);
    
    $reviewId = (int) $pdo->lastInsertId();
    
    cc_notify_admin($pdo, [
        'sender_id' => $userId,
        'type' => 'review',
        'title' => 'New Review Pending',
        'message' => 'A customer has submitted a new review that requires your approval.',
        'related_entity_id' => $reviewId,
    ]);
    cc_notify_customer($pdo, (int) $userId, [
        'sender_id' => $userId,
        'type' => 'review',
        'title' => 'Review Pending',
        'message' => 'Your review has been successfully submitted and is currently pending approval.',
        'related_entity_id' => $reviewId,
    ]);
    if ($barber_id) {
        cc_notify_barber($pdo, (int) $barber_id, [
            'sender_id' => $userId,
            'type' => 'review',
            'title' => 'New Review Received',
            'message' => 'A customer submitted a review for your service (pending approval).',
            'related_entity_id' => $reviewId,
        ]);
    }

    echo json_encode(['success' => true, 'message' => 'Review submitted for approval']);
    exit;
}

// Checkout (Mock Payment) - Initiates payment, but requires receipt upload
if ($method === 'POST' && $path === '/customer/checkout') {
    $data = json_decode(file_get_contents('php://input'), true);
    $appointment_id = $data['appointment_id'] ?? 0;
    $amount = $data['amount'] ?? 0;
    $payment_method = $data['payment_method'] ?? 'bank_transfer';
    
    if (!$appointment_id || !$amount) {
        http_response_code(400); echo json_encode(['error' => 'Invalid payment data']); exit;
    }
    
    $transaction_ref = 'TXN_' . strtoupper(uniqid());
    
    // Check if payment already exists
    $stmt = $pdo->prepare("SELECT id FROM payments WHERE appointment_id = ?");
    $stmt->execute([$appointment_id]);
    $existing = $stmt->fetchColumn();
    
    if ($existing) {
        $stmt = $pdo->prepare("UPDATE payments SET amount = ?, payment_method = ?, transaction_ref = ?, status = 'pending' WHERE appointment_id = ?");
        $stmt->execute([$amount, $payment_method, $transaction_ref, $appointment_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO payments (customer_id, appointment_id, amount, status, payment_method, transaction_ref) VALUES (?, ?, ?, 'pending', ?, ?)");
        $stmt->execute([$userId, $appointment_id, $amount, $payment_method, $transaction_ref]);
    }
    
    echo json_encode(['success' => true, 'transaction_ref' => $transaction_ref, 'message' => 'Payment initiated. Please upload receipt.']);
    exit;
}

// Get Payment Details (Barber's Bank Account)
if ($method === 'GET' && preg_match('/^\/customer\/checkout\/(\d+)\/payment-details$/', $path, $matches)) {
    $appointment_id = $matches[1];
    
    // Ensure the appointment belongs to the customer
    $stmt = $pdo->prepare("SELECT barber_id FROM appointments WHERE id = ? AND customer_id = ?");
    $stmt->execute([$appointment_id, $userId]);
    $barber_id = $stmt->fetchColumn();
    
    if (!$barber_id) {
        http_response_code(404); echo json_encode(['error' => 'Appointment not found']); exit;
    }
    
    // Get barber bank details
    $stmt = $pdo->prepare("SELECT bank_name, account_name, account_number FROM barbers WHERE id = ?");
    $stmt->execute([$barber_id]);
    $bankDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['data' => $bankDetails]);
    exit;
}

// Upload Receipt
if ($method === 'POST' && preg_match('/^\/customer\/checkout\/(\d+)\/receipt$/', $path, $matches)) {
    $appointment_id = $matches[1];
    
    if (!isset($_FILES['receipt'])) {
        http_response_code(400); echo json_encode(['error' => 'No receipt uploaded']); exit;
    }
    
    $file = $_FILES['receipt'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        http_response_code(400); echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, and PDF allowed.']); exit;
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'receipt_' . $appointment_id . '_' . time() . '.' . $ext;
    $uploadPath = __DIR__ . '/uploads/receipts/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $receipt_url = '/public/uploads/receipts/' . $filename;
        
        // Update payment status
        $stmt = $pdo->prepare("UPDATE payments SET receipt_image = ?, status = 'awaiting_verification' WHERE appointment_id = ? AND customer_id = ?");
        $stmt->execute([$receipt_url, $appointment_id, $userId]);
        
        // Get barber ID for this appointment to send notification
        $stmt = $pdo->prepare("SELECT barber_id FROM appointments WHERE id = ?");
        $stmt->execute([$appointment_id]);
        $barber_id = $stmt->fetchColumn();
        
        if ($barber_id) {
            cc_notify_barber($pdo, (int) $barber_id, [
                'sender_id' => $userId,
                'type' => 'payment',
                'title' => 'Payment Receipt Uploaded',
                'message' => 'A customer has uploaded a payment receipt for verification.',
                'related_entity_id' => (int) $appointment_id,
            ]);
            cc_notify_admin($pdo, [
                'sender_id' => $userId,
                'type' => 'payment',
                'title' => 'Payment Receipt Uploaded',
                'message' => 'A customer has uploaded a payment receipt for verification.',
                'related_entity_id' => (int) $appointment_id,
            ]);
        }
        cc_notify_customer($pdo, (int) $userId, [
            'sender_id' => $userId,
            'type' => 'payment',
            'title' => 'Receipt Uploaded',
            'message' => 'Your payment receipt was uploaded and is awaiting verification.',
            'related_entity_id' => (int) $appointment_id,
        ]);
        
        // Log in audit_logs
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'upload_receipt', 'payment', ?, ?)");
        $stmt->execute([$userId, $appointment_id, $_SERVER['REMOTE_ADDR']]);
        
        echo json_encode(['success' => true, 'receipt_url' => $receipt_url, 'message' => 'Receipt uploaded successfully. Awaiting verification.']);
    } else {
        http_response_code(500); echo json_encode(['error' => 'Failed to save receipt file']);
    }
    exit;
}

// ==========================================
// MY CODES
// ==========================================
if ($method === 'GET' && $path === '/customer/my-codes') {
    $stmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.verification_code, 
               s.name as service_name, s.image as service_image, 
               b.name as barber_name, b.avatar as barber_avatar
        FROM appointments a
        LEFT JOIN services s ON a.service_id = s.id
        LEFT JOIN barbers b_model ON a.barber_id = b_model.id
        LEFT JOIN users b ON b_model.user_id = b.id
        WHERE a.customer_id = ? AND a.verification_code IS NOT NULL AND a.verification_code != ''
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->execute([$userId]);
    $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['data' => $codes]);
    exit;
}
