<?php
// Barber API Routes

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

// Ensure user is barber and get barber_id
$stmt = $pdo->prepare("SELECT id FROM barbers WHERE user_id = ?");
$stmt->execute([$userId]);
$barberId = $stmt->fetchColumn();

if (!$barberId) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized: Barber access required']);
    exit;
}

// ==========================================
// BARBER OWN STATUS & AVAILABILITY
// ==========================================
if (!function_exists('generateVerificationCode')) {
    function generateVerificationCode($pdo, $barberId) {
        $stmt = $pdo->prepare("SELECT u.name FROM barbers b JOIN users u ON b.user_id = u.id WHERE b.id = ?");
        $stmt->execute([$barberId]);
        $bName = $stmt->fetchColumn() ?: 'BA';
        $initials = '';
        foreach (explode(' ', trim($bName)) as $w) { if(!empty($w)) $initials .= strtoupper($w[0]); }
        $initials = substr($initials ?: 'BA', 0, 2);
        return $initials . date('dmy') . sprintf('%04d', mt_rand(1000, 9999));
    }
}
if ($method === 'GET' && $path === '/barber/my-status') {
    $stmt = $pdo->prepare("SELECT b.status, b.is_available, u.name FROM barbers b INNER JOIN users u ON b.user_id = u.id WHERE b.id = ?");
    $stmt->execute([$barberId]);
    echo json_encode(['data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
    exit;
}

if ($method === 'PATCH' && $path === '/barber/my-status') {
    $data = json_decode(file_get_contents('php://input'), true);
    $newStatus = $data['status'] ?? null;
    $is_available = isset($data['is_available']) ? ($data['is_available'] ? 1 : 0) : null;
    
    // Check current status first
    $stmt = $pdo->prepare("SELECT status FROM barbers WHERE id = ?");
    $stmt->execute([$barberId]);
    $currentStatus = $stmt->fetchColumn();
    
    if ($currentStatus === 'suspended' || $currentStatus === 'pending_approval') {
        http_response_code(403); echo json_encode(['error' => 'You cannot change your status while your account is ' . str_replace('_', ' ', $currentStatus)]); exit;
    }
    
    $allowed = ['active', 'on_leave'];
    if ($newStatus && !in_array($newStatus, $allowed)) {
        http_response_code(400); echo json_encode(['error' => 'You can only set your status to Active or Not Active']); exit;
    }
    
    if ($newStatus) {
        $pdo->prepare("UPDATE barbers SET status = ? WHERE id = ?")->execute([$newStatus, $barberId]);
    }
    if ($is_available !== null) {
        $pdo->prepare("UPDATE barbers SET is_available = ? WHERE id = ?")->execute([$is_available, $barberId]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Status updated']);
    exit;
}

// ==========================================
// BARBER ACCOUNT DETAILS
// ==========================================
if ($method === 'GET' && $path === '/barber/account') {
    $stmt = $pdo->prepare("SELECT bank_name, account_name, account_number FROM barbers WHERE id = ?");
    $stmt->execute([$barberId]);
    echo json_encode(['data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
    exit;
}

if ($method === 'PATCH' && $path === '/barber/account') {
    $data = json_decode(file_get_contents('php://input'), true);
    $bank_name = $data['bank_name'] ?? '';
    $account_name = $data['account_name'] ?? '';
    $account_number = $data['account_number'] ?? '';
    
    $stmt = $pdo->prepare("UPDATE barbers SET bank_name = ?, account_name = ?, account_number = ? WHERE id = ?");
    $stmt->execute([$bank_name, $account_name, $account_number, $barberId]);
    
    echo json_encode(['success' => true, 'message' => 'Account details updated']);
    exit;
}

// ==========================================
// BARBER PROFILE
// ==========================================
if ($method === 'GET' && $path === '/barber/profile') {
    $stmt = $pdo->prepare("
        SELECT u.name, u.email, u.phone, b.bio, b.specialties, b.rating, u.avatar as profile_image 
        FROM barbers b 
        INNER JOIN users u ON b.user_id = u.id 
        WHERE b.id = ?
    ");
    $stmt->execute([$barberId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($profile && $profile['specialties']) {
        $profile['specialties'] = json_decode($profile['specialties'], true);
    }
    echo json_encode(['data' => $profile]);
    exit;
}

if ($method === 'POST' && $path === '/barber/profile') {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if (strpos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        $data = $_POST;
    }

    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    $bio = $data['bio'] ?? '';
    $instagram_url = $data['instagram_url'] ?? '';
    
    if (isset($data['specialties'])) {
        $specialties = is_string($data['specialties']) ? $data['specialties'] : json_encode($data['specialties']);
    } else {
        $specialties = null;
    }

    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $existingImage = $stmt->fetchColumn();
    $profile_image = $existingImage;

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/barbers/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $filename = 'barber_' . $barberId . '_' . time() . '.' . $ext;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $filename)) {
            $profile_image = '/uploads/barbers/' . $filename;
            // Delete old avatar if exists
            if ($existingImage && file_exists(__DIR__ . $existingImage)) {
                @unlink(__DIR__ . $existingImage);
            }
        }
    }

    // Update users table (name, phone, avatar)
    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?");
    $stmt->execute([$name, $phone, $profile_image, $userId]);

    // Update barbers table
    $stmt = $pdo->prepare("UPDATE barbers SET bio = ?, specialties = ? WHERE id = ?");
    $stmt->execute([$bio, $specialties, $barberId]);

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully', 'profile_image' => $profile_image]);
    exit;
}


// ==========================================
// BARBER DASHBOARD
// ==========================================
if ($method === 'GET' && $path === '/barber/dashboard') {
    $stats = [];
    
    // Total Appointments Today
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE barber_id = ? AND appointment_date = CURRENT_DATE AND status IN ('pending', 'confirmed')");
    $stmt->execute([$barberId]);
    $stats['today_appointments'] = (int)$stmt->fetchColumn();
    
    // Completed Appointments (All Time or Month)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE barber_id = ? AND status = 'completed'");
    $stmt->execute([$barberId]);
    $stats['completed_appointments'] = (int)$stmt->fetchColumn();
    
    // Total Revenue
    $stmt = $pdo->prepare("SELECT SUM(p.amount) FROM payments p JOIN appointments a ON p.appointment_id = a.id WHERE a.barber_id = ? AND p.status = 'successful'");
    $stmt->execute([$barberId]);
    $stats['total_revenue'] = (float)$stmt->fetchColumn();
    
    // Rating
    $stmt = $pdo->prepare("SELECT rating FROM barbers WHERE id = ?");
    $stmt->execute([$barberId]);
    $stats['rating'] = (float)$stmt->fetchColumn();
    
    // Recent Bookings
    $stmt = $pdo->prepare("SELECT a.id, a.appointment_date, a.appointment_time, a.status, s.name as service_name, u.name as customer_name 
                           FROM appointments a 
                           LEFT JOIN services s ON a.service_id = s.id 
                           LEFT JOIN users u ON a.customer_id = u.id
                           WHERE a.barber_id = ? 
                           ORDER BY a.created_at DESC LIMIT 5");
    $stmt->execute([$barberId]);
    $recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($recent_bookings as &$booking) {
        $booking['service'] = ['name' => $booking['service_name']];
        $booking['customer_name'] = $booking['customer_name'];
        unset($booking['service_name']);
    }
    
    // Pending Payments
    $stmt = $pdo->prepare("SELECT a.id, a.appointment_date, a.appointment_time, u.name as customer_name, s.name as service_name, s.price, p.status as payment_status, p.receipt_image
                           FROM appointments a
                           INNER JOIN payments p ON a.id = p.appointment_id
                           INNER JOIN users u ON a.customer_id = u.id
                           INNER JOIN services s ON a.service_id = s.id
                           WHERE a.barber_id = ? AND p.status = 'awaiting_verification'");
    $stmt->execute([$barberId]);
    $pending_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['data' => [
        'stats' => $stats,
        'recent_bookings' => $recent_bookings,
        'pending_payments' => $pending_payments
    ]]);
    exit;
}

// ==========================================
// BARBER ANALYTICS
// ==========================================
if ($method === 'GET' && $path === '/barber/analytics') {
    $range = $_GET['range'] ?? '30d';
    
    // Determine date filter based on range
    $df = "";
    $dfA = "";
    $dfP = "";
    if ($range === '7d') {
        $df = "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $dfA = "AND a.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $dfP = "AND p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } else if ($range === '30d') {
        $df = "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $dfA = "AND a.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $dfP = "AND p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    } else if ($range === 'month') {
        $df = "AND created_at >= DATE_FORMAT(NOW() ,'%Y-%m-01')";
        $dfA = "AND a.created_at >= DATE_FORMAT(NOW() ,'%Y-%m-01')";
        $dfP = "AND p.created_at >= DATE_FORMAT(NOW() ,'%Y-%m-01')";
    }

    $analytics = [
        'performance_metrics' => [],
        'revenue_trends' => [],
        'status_breakdown' => [],
        'top_services' => [],
        'busiest_days' => []
    ];

    // Performance Metrics
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(p.amount), 0) as total_revenue FROM payments p JOIN appointments a ON p.appointment_id = a.id WHERE a.barber_id = ? AND p.status = 'successful' $dfP");
    $stmt->execute([$barberId]);
    $analytics['performance_metrics']['total_revenue'] = (float)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT customer_id) FROM appointments WHERE barber_id = ? $df");
    $stmt->execute([$barberId]);
    $analytics['performance_metrics']['clients_served'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed FROM appointments WHERE barber_id = ? $df");
    $stmt->execute([$barberId]);
    $rates = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = (int)$rates['total'];
    $completed = (int)$rates['completed'];
    $analytics['performance_metrics']['completion_rate'] = $total > 0 ? round(($completed / $total) * 100) : 0;

    $stmt = $pdo->prepare("SELECT rating FROM barbers WHERE id = ?");
    $stmt->execute([$barberId]);
    $analytics['performance_metrics']['avg_rating'] = (float)$stmt->fetchColumn();

    // Top Services
    $stmt = $pdo->prepare("
        SELECT s.name, COUNT(a.id) as count, COALESCE(SUM(p.amount), 0) as revenue 
        FROM appointments a 
        JOIN services s ON a.service_id = s.id 
        LEFT JOIN payments p ON p.appointment_id = a.id AND p.status = 'successful'
        WHERE a.barber_id = ? AND a.status = 'completed' $dfA 
        GROUP BY s.id 
        ORDER BY count DESC 
        LIMIT 5
    ");
    $stmt->execute([$barberId]);
    $analytics['top_services'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Status Breakdown
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM appointments WHERE barber_id = ? $df GROUP BY status");
    $stmt->execute([$barberId]);
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $breakdown = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0];
    foreach ($statuses as $s) {
        $breakdown[$s['status']] = (int)$s['count'];
    }
    $analytics['status_breakdown'] = $breakdown;

    // Revenue Trends (last 6 months)
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(a.appointment_date, '%b') as month, COALESCE(SUM(p.amount), 0) as revenue 
        FROM appointments a
        LEFT JOIN payments p ON p.appointment_id = a.id AND p.status = 'successful'
        WHERE a.barber_id = ? AND a.appointment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH) AND a.status = 'completed'
        GROUP BY YEAR(a.appointment_date), MONTH(a.appointment_date), month
        ORDER BY YEAR(a.appointment_date) ASC, MONTH(a.appointment_date) ASC
    ");
    $stmt->execute([$barberId]);
    $analytics['revenue_trends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Busiest Days
    $stmt = $pdo->prepare("
        SELECT DAYNAME(appointment_date) as day, COUNT(*) as count 
        FROM appointments 
        WHERE barber_id = ? AND status IN ('completed', 'confirmed', 'pending') $df
        GROUP BY DAYOFWEEK(appointment_date), day
        ORDER BY count DESC
    ");
    $stmt->execute([$barberId]);
    $analytics['busiest_days'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $analytics]);
    exit;
}

// ==========================================
// BARBER SCHEDULE
// ==========================================
if ($method === 'GET' && $path === '/barber/schedule') {
    $schedule = [];
    
    // Get working hours
    $stmt = $pdo->prepare("SELECT id, day_of_week, start_time as open_time, end_time as close_time, is_available as is_closed FROM working_hours WHERE barber_id = ? ORDER BY day_of_week ASC");
    $stmt->execute([$barberId]);
    $hours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Note: the schema might have is_available. If 1 it means open, if 0 it means closed.
    // In our query we aliased is_available as is_closed for now, but let's fix it:
    foreach ($hours as &$hour) {
        $hour['is_closed'] = !$hour['is_closed']; // convert is_available to is_closed boolean
        $hour['open_time'] = substr($hour['open_time'], 0, 5); // '09:00:00' to '09:00'
        $hour['close_time'] = substr($hour['close_time'], 0, 5);
    }
    $schedule['working_hours'] = $hours;
    
    // Get upcoming appointments (today and future)
    $stmt = $pdo->prepare("SELECT a.id, a.appointment_date, a.appointment_time, u.name as client_name, s.name as service_name 
                           FROM appointments a 
                           JOIN users u ON a.customer_id = u.id 
                           JOIN services s ON a.service_id = s.id 
                           WHERE a.barber_id = ? AND a.appointment_date >= CURRENT_DATE AND a.status IN ('confirmed', 'pending')
                           ORDER BY a.appointment_date ASC, a.appointment_time ASC LIMIT 10");
    $stmt->execute([$barberId]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // format for frontend
    foreach ($appointments as &$appt) {
        $appt['service'] = ['name' => $appt['service_name']];
        $appt['appointment_time'] = substr($appt['appointment_time'], 0, 5);
    }
    $schedule['appointments'] = $appointments;
    
    echo json_encode(['data' => $schedule]);
    exit;
}

if ($method === 'PUT' && $path === '/barber/schedule') {
    $data = json_decode(file_get_contents('php://input'), true);
    $working_hours = $data['working_hours'] ?? [];
    
    $pdo->beginTransaction();
    try {
        foreach ($working_hours as $hour) {
            $is_available = empty($hour['is_closed']) ? 1 : 0;
            $start = $hour['open_time'] ?? '09:00';
            $end = $hour['close_time'] ?? '17:00';
            $day = $hour['day_of_week'];
            
            // Check if exists
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
        echo json_encode(['success' => true, 'message' => 'Schedule updated successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update schedule']);
    }
    exit;
}

// ==========================================
// BARBER NOTIFICATIONS TO CUSTOMERS
// ==========================================
if ($method === 'POST' && $path === '/barber/notifications') {
    $data = json_decode(file_get_contents('php://input'), true);
    $title = $data['title'] ?? '';
    $message = $data['message'] ?? '';
    
    if (!$title || !$message) {
        http_response_code(400); echo json_encode(['error' => 'Title and message required']); exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, title, message) VALUES (?, 'all_customers', ?, ?)");
    $stmt->execute([$userId, $title, $message]);
    echo json_encode(['success' => true, 'message' => 'Notification sent to all customers']);
    exit;
}

if ($method === 'GET' && $path === '/barber/notifications') {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE sender_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$userId]);
    echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

// ==========================================
// SERVICES MANAGEMENT
// ==========================================
if (strpos($path, '/barber/services') === 0) {
    // GET all services for this barber (and maybe globals?)
    // Let's just return all services they own, plus globals so they can see them, or just their own.
    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT s.*, c.name as category_name FROM services s LEFT JOIN service_categories c ON s.category_id = c.id WHERE s.barber_id = ? ORDER BY s.created_at DESC");
        $stmt->execute([$barberId]);
        echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }
    
    // POST new service
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $category_id = $data['category_id'] ?? 1; // Default category
        $price = $data['price'] ?? 0;
        $duration = $data['duration_minutes'] ?? 30;
        
        if (!$name || $price <= 0) {
            http_response_code(400); echo json_encode(['error' => 'Name and valid price required']); exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO services (barber_id, name, description, category_id, price, duration_minutes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$barberId, $name, $description, $category_id, $price, $duration]);
        echo json_encode(['success' => true, 'message' => 'Service created']);
        exit;
    }

    // PUT update service
    if ($method === 'PUT' && preg_match('/^\/barber\/services\/(\d+)$/', $path, $matches)) {
        $id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $price = $data['price'] ?? 0;
        $duration = $data['duration_minutes'] ?? 30;
        $is_available = isset($data['is_available']) ? ($data['is_available'] ? 1 : 0) : 1;
        
        $stmt = $pdo->prepare("UPDATE services SET name = ?, price = ?, duration_minutes = ?, is_available = ? WHERE id = ? AND barber_id = ?");
        $stmt->execute([$name, $price, $duration, $is_available, $id, $barberId]);
        echo json_encode(['success' => true, 'message' => 'Service updated']);
        exit;
    }

    // DELETE service
    if ($method === 'DELETE' && preg_match('/^\/barber\/services\/(\d+)$/', $path, $matches)) {
        $id = $matches[1];
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ? AND barber_id = ?");
        $stmt->execute([$id, $barberId]);
        echo json_encode(['success' => true, 'message' => 'Service deleted']);
        exit;
    }
}

// ==========================================
// GALLERY MANAGEMENT
// ==========================================
if (strpos($path, '/barber/gallery') === 0) {
    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT * FROM gallery WHERE barber_id = ? ORDER BY created_at DESC");
        $stmt->execute([$barberId]);
        echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }
    
    if ($method === 'POST' && preg_match('/^\/barber\/gallery$/', $path)) {
        file_put_contents('debug.log', print_r($_POST, true) . print_r($_FILES, true), FILE_APPEND);
        $title = $_POST['title'] ?? 'Style';
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? 'Haircut';
        $image_url = '';

        if (isset($_FILES['image'])) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/uploads/gallery/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'gallery_' . $barberId . '_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                    $image_url = '/uploads/gallery/' . $filename;
                } else {
                    http_response_code(500); echo json_encode(['error' => 'Failed to move uploaded file. Check permissions for: ' . $uploadDir]); exit;
                }
            } else {
                $errCode = $_FILES['image']['error'];
                $errMsg = 'Image upload failed with error code: ' . $errCode;
                if ($errCode === UPLOAD_ERR_INI_SIZE) $errMsg = 'Image file is too large (exceeds server limit).';
                http_response_code(400); echo json_encode(['error' => $errMsg]); exit;
            }
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data) {
                $image_url = $data['image_url'] ?? '';
                $title = $data['title'] ?? $title;
                $description = $data['description'] ?? $description;
                $category = $data['category'] ?? $category;
            } else {
                $image_url = $_POST['image_url'] ?? '';
            }
        }
        
        if (!$image_url) {
            http_response_code(400); echo json_encode(['error' => 'Image file or URL required']); exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO gallery (barber_id, image_path, title, description, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$barberId, $image_url, $title, $description, $category]);
        echo json_encode(['success' => true, 'message' => 'Image added to gallery']);
        exit;
    }

    if ($method === 'DELETE' && preg_match('/^\/barber\/gallery\/(\d+)$/', $path, $matches)) {
        $id = $matches[1];
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ? AND barber_id = ?");
        $stmt->execute([$id, $barberId]);
        echo json_encode(['success' => true, 'message' => 'Image deleted']);
        exit;
    }

    if (($method === 'PUT' || $method === 'POST') && preg_match('/^\/barber\/gallery\/(\d+)$/', $path, $matches)) {
        $id = $matches[1];
        
        // Fetch existing
        $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ? AND barber_id = ?");
        $stmt->execute([$id, $barberId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$existing) {
            http_response_code(404); echo json_encode(['error' => 'Image not found']); exit;
        }
        $image_url = $existing['image_path'];

        $title = $_POST['title'] ?? null;
        $description = $_POST['description'] ?? null;
        $category = $_POST['category'] ?? null;

        if (isset($_FILES['image'])) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/uploads/gallery/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'gallery_' . $barberId . '_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                    $image_url = '/uploads/gallery/' . $filename;
                } else {
                    http_response_code(500); echo json_encode(['error' => 'Failed to move uploaded file. Check permissions for: ' . $uploadDir]); exit;
                }
            } else {
                $errCode = $_FILES['image']['error'];
                $errMsg = 'Image upload failed with error code: ' . $errCode;
                if ($errCode === UPLOAD_ERR_INI_SIZE) $errMsg = 'Image file is too large (exceeds server limit).';
                http_response_code(400); echo json_encode(['error' => $errMsg]); exit;
            }
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data) {
                if (isset($data['image_url'])) $image_url = $data['image_url'];
                if (isset($data['title'])) $title = $data['title'];
                if (isset($data['description'])) $description = $data['description'];
                if (isset($data['category'])) $category = $data['category'];
            }
        }

        // if using POST, form fields might be set
        if ($title === null) $title = $_POST['title'] ?? 'Style';
        if ($description === null) $description = $_POST['description'] ?? '';
        if ($category === null) $category = $_POST['category'] ?? 'Haircut';

        $stmt = $pdo->prepare("UPDATE gallery SET image_path = ?, title = ?, description = ?, category = ? WHERE id = ? AND barber_id = ?");
        $stmt->execute([$image_url, $title, $description, $category, $id, $barberId]);
        echo json_encode(['success' => true, 'message' => 'Image updated']);
        exit;
    }
}

// ==========================================
// BLOG MANAGEMENT
// ==========================================
if (strpos($path, '/barber/blog') === 0) {
    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT p.*,
            (SELECT SUM(CASE WHEN reaction_type = 'love' THEN 1 ELSE 0 END) FROM blog_reactions WHERE post_id = p.id) as loves_count,
            (SELECT SUM(CASE WHEN reaction_type = 'dislike' THEN 1 ELSE 0 END) FROM blog_reactions WHERE post_id = p.id) as dislikes_count
            FROM blog_posts p ORDER BY p.created_at DESC");
        $stmt->execute(); 
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Cast to int
        foreach ($posts as &$post) {
            $post['loves_count'] = (int)$post['loves_count'];
            $post['dislikes_count'] = (int)$post['dislikes_count'];
        }
        echo json_encode(['data' => $posts]);
        exit;
    }
    
    // UPDATE - must be checked before CREATE since POST is used for both
    if (($method === 'PUT' || $method === 'POST') && preg_match('/^\/barber\/blog\/(\d+)$/', $path, $matches)) {
        $id = $matches[1];
        
        $stmt = $pdo->prepare("SELECT featured_image FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$existing) {
            http_response_code(404); echo json_encode(['error' => 'Post not found']); exit;
        }
        $featured_image = $existing['featured_image'];

        $title = $_POST['title'] ?? null;
        $content = $_POST['content'] ?? null;
        $excerpt = $_POST['excerpt'] ?? null;
        $is_published = isset($_POST['is_published']) ? ($_POST['is_published'] ? 1 : 0) : null;

        if (isset($_FILES['image'])) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/uploads/blog/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'blog_' . $barberId . '_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                    $featured_image = '/uploads/blog/' . $filename;
                } else {
                    http_response_code(500); echo json_encode(['error' => 'Failed to move uploaded file']); exit;
                }
            } else {
                $errCode = $_FILES['image']['error'];
                $errMsg = 'Image upload failed with error code: ' . $errCode;
                if ($errCode === UPLOAD_ERR_INI_SIZE) $errMsg = 'Image file is too large.';
                http_response_code(400); echo json_encode(['error' => $errMsg]); exit;
            }
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data) {
                if (isset($data['title'])) $title = $data['title'];
                if (isset($data['content'])) $content = $data['content'];
                if (isset($data['excerpt'])) $excerpt = $data['excerpt'];
                if (isset($data['is_published'])) $is_published = $data['is_published'] ? 1 : 0;
                if (isset($data['featured_image'])) $featured_image = $data['featured_image'];
            }
        }

        if ($title === null) $title = $_POST['title'] ?? '';
        if ($content === null) $content = $_POST['content'] ?? '';
        if ($excerpt === null) $excerpt = $_POST['excerpt'] ?? '';
        if ($is_published === null) $is_published = isset($_POST['is_published']) ? ($_POST['is_published'] ? 1 : 0) : 0;

        $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, content = ?, excerpt = ?, is_published = ?, featured_image = ? WHERE id = ?");
        $stmt->execute([$title, $content, $excerpt, $is_published, $featured_image, $id]);
        echo json_encode(['success' => true, 'message' => 'Blog post updated']);
        exit;
    }

    // CREATE - only matches exact /barber/blog path
    if ($method === 'POST' && $path === '/barber/blog') {
        $title = $_POST['title'] ?? null;
        $content = $_POST['content'] ?? null;
        $excerpt = $_POST['excerpt'] ?? '';
        $is_published = isset($_POST['is_published']) ? ($_POST['is_published'] ? 1 : 0) : 0;
        $featured_image = null;

        if (isset($_FILES['image'])) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/uploads/blog/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'blog_' . $barberId . '_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                    $featured_image = '/uploads/blog/' . $filename;
                } else {
                    http_response_code(500); echo json_encode(['error' => 'Failed to move uploaded file']); exit;
                }
            } else {
                $errCode = $_FILES['image']['error'];
                $errMsg = 'Image upload failed with error code: ' . $errCode;
                if ($errCode === UPLOAD_ERR_INI_SIZE) $errMsg = 'Image file is too large.';
                http_response_code(400); echo json_encode(['error' => $errMsg]); exit;
            }
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data) {
                $title = $data['title'] ?? $title;
                $content = $data['content'] ?? $content;
                $excerpt = $data['excerpt'] ?? $excerpt;
                $is_published = isset($data['is_published']) ? ($data['is_published'] ? 1 : 0) : $is_published;
                $featured_image = $data['featured_image'] ?? null;
            }
        }
        
        if (!$title || !$content) {
            http_response_code(400); echo json_encode(['error' => 'Title and content required']); exit;
        }
        
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . time();
        
        $stmt = $pdo->prepare("INSERT INTO blog_posts (author_id, title, slug, content, excerpt, is_published, featured_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $slug, $content, $excerpt, $is_published, $featured_image]);
        
        if ($is_published) {
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message) VALUES (?, 'all_customers', NULL, 'blog_update', 'New Blog Post', ?)");
            $stmt->execute([$userId, "A new blog post '$title' has been published!"]);
        }

        echo json_encode(['success' => true, 'message' => 'Blog post created']);
        exit;
    }

    if ($method === 'DELETE' && preg_match('/^\/barber\/blog\/(\d+)$/', $path, $matches)) {
        $id = $matches[1];
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Blog post deleted']);
        exit;
    }
}

// ==========================================
// APPOINTMENTS & PAYMENT VERIFICATION
// ==========================================
if (strpos($path, '/barber/bookings') === 0) {
    // GET all bookings for this barber
    if ($method === 'GET' && $path === '/barber/bookings') {
        $stmt = $pdo->prepare("
            SELECT a.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                   s.name as service_name, s.price, s.duration_minutes,
                   p.status as payment_status, p.receipt_image, p.transaction_ref, p.verified_by, p.payment_method
            FROM appointments a
            INNER JOIN users u ON a.customer_id = u.id
            INNER JOIN services s ON a.service_id = s.id
            LEFT JOIN payments p ON a.id = p.appointment_id
            WHERE a.barber_id = ? OR a.barber_id IS NULL
            ORDER BY a.id DESC
        ");
        $stmt->execute([$barberId]);
        echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }
    
    // POST verify payment
    if ($method === 'POST' && preg_match('/^\/barber\/bookings\/(\d+)\/verify-payment$/', $path, $matches)) {
        $appointmentId = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? 'approve'; // 'approve' or 'reject'
        
        // Ensure this appointment belongs to the barber or is unassigned
        $stmt = $pdo->prepare("SELECT customer_id, status, barber_id FROM appointments WHERE id = ? AND (barber_id = ? OR barber_id IS NULL)");
        $stmt->execute([$appointmentId, $barberId]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$appointment) {
            http_response_code(404); echo json_encode(['error' => 'Booking not found or not yours']); exit;
        }
        
        if ($action === 'approve') {
            // Update payment and appointment
            $verificationCode = generateVerificationCode($pdo, $barberId);
            $pdo->prepare("UPDATE payments SET status = 'successful', verified_by = 'barber' WHERE appointment_id = ?")->execute([$appointmentId]);
            $pdo->prepare("UPDATE appointments SET status = 'confirmed', barber_id = COALESCE(barber_id, ?), verification_code = ? WHERE id = ?")->execute([$barberId, $verificationCode, $appointmentId]);
            
            // Notify customer
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message, related_entity_id) VALUES (?, 'customer', ?, 'payment', 'Payment Verified', 'Your payment has been verified and your booking is confirmed.', ?)");
            $stmt->execute([$userId, $appointment['customer_id'], $appointmentId]);
            
            // Notify admin
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, type, title, message, related_entity_id) VALUES (?, 'admin', 'payment', 'Payment Verified by Barber', 'Barber has verified the payment and confirmed booking.', ?)");
            $stmt->execute([$userId, $appointmentId]);
            
            // Audit Log
            $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'verify_payment', 'payment', ?, ?)")->execute([$userId, $appointmentId, $_SERVER['REMOTE_ADDR']]);
            
            echo json_encode(['success' => true, 'message' => 'Payment verified and booking confirmed']);
        } else {
            // Reject
            $pdo->prepare("UPDATE payments SET status = 'failed', verified_by = 'barber' WHERE appointment_id = ?")->execute([$appointmentId]);
            // Maybe cancel booking or keep pending
            $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?")->execute([$appointmentId]);
            
            // Notify customer
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message, related_entity_id) VALUES (?, 'customer', ?, 'payment', 'Payment Rejected', 'Your payment receipt was rejected. The booking has been cancelled.', ?)");
            $stmt->execute([$userId, $appointment['customer_id'], $appointmentId]);
            
            // Notify admin
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, type, title, message, related_entity_id) VALUES (?, 'admin', 'payment', 'Payment Rejected by Barber', 'Barber has rejected the payment receipt and cancelled the booking.', ?)");
            $stmt->execute([$userId, $appointmentId]);
            
            // Audit Log
            $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'reject_payment', 'payment', ?, ?)")->execute([$userId, $appointmentId, $_SERVER['REMOTE_ADDR']]);
            
            echo json_encode(['success' => true, 'message' => 'Payment rejected']);
        }
        exit;
    }
}

// ==========================================
// APPOINTMENTS MANAGEMENT
// ==========================================
if (strpos($path, '/barber/appointments') === 0) {
    // PATCH /barber/appointments/{id}/approve
    if ($method === 'PATCH' && preg_match('/^\/barber\/appointments\/(\d+)\/approve$/', $path, $matches)) {
        $appointmentId = $matches[1];
        
        // Ensure this appointment belongs to the barber or is unassigned
        $stmt = $pdo->prepare("SELECT customer_id, status FROM appointments WHERE id = ? AND (barber_id = ? OR barber_id IS NULL)");
        $stmt->execute([$appointmentId, $barberId]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$appointment) {
            http_response_code(404); echo json_encode(['error' => 'Booking not found or not yours']); exit;
        }
        
        $pdo->beginTransaction();
        try {
            $verificationCode = generateVerificationCode($pdo, $barberId);
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'confirmed', barber_id = COALESCE(barber_id, ?), verification_code = ? WHERE id = ?");
            $stmt->execute([$barberId, $verificationCode, $appointmentId]);
            
            $stmt = $pdo->prepare("UPDATE payments SET status = 'successful' WHERE appointment_id = ?");
            $stmt->execute([$appointmentId]);
            
            // Notify customer
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message, related_entity_id) VALUES (?, 'customer', ?, 'booking', 'Booking Confirmed', 'Your booking has been confirmed by the barber.', ?)");
            $stmt->execute([$userId, $appointment['customer_id'], $appointmentId]);
            
            // Notify admin
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, type, title, message, related_entity_id) VALUES (?, 'admin', 'booking', 'Booking Confirmed by Barber', 'Barber has confirmed the booking.', ?)");
            $stmt->execute([$userId, $appointmentId]);
            
            // Audit Log
            $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'approve_booking', 'appointment', ?, ?)");
            $stmt->execute([$userId, $appointmentId, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Appointment approved']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500); echo json_encode(['error' => 'Failed to approve: ' . $e->getMessage()]);
        }
        exit;
    }
    
    // PATCH /barber/appointments/{id}/cancel
    if ($method === 'PATCH' && preg_match('/^\/barber\/appointments\/(\d+)\/cancel$/', $path, $matches)) {
        $appointmentId = $matches[1];
        
        $stmt = $pdo->prepare("SELECT customer_id FROM appointments WHERE id = ? AND (barber_id = ? OR barber_id IS NULL)");
        $stmt->execute([$appointmentId, $barberId]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$appointment) {
            http_response_code(404); echo json_encode(['error' => 'Booking not found or not yours']); exit;
        }
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$appointmentId]);
            
            $stmt = $pdo->prepare("UPDATE payments SET status = 'failed' WHERE appointment_id = ?");
            $stmt->execute([$appointmentId]);
            
            // Notify customer
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message, related_entity_id) VALUES (?, 'customer', ?, 'booking', 'Booking Cancelled', 'Your booking has been cancelled by the barber.', ?)");
            $stmt->execute([$userId, $appointment['customer_id'], $appointmentId]);
            
            // Notify admin
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, type, title, message, related_entity_id) VALUES (?, 'admin', 'booking', 'Booking Cancelled by Barber', 'Barber has cancelled the booking.', ?)");
            $stmt->execute([$userId, $appointmentId]);
            
            // Audit Log
            $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'cancel_booking', 'appointment', ?, ?)");
            $stmt->execute([$userId, $appointmentId, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Appointment cancelled']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500); echo json_encode(['error' => 'Failed to cancel: ' . $e->getMessage()]);
        }
        exit;
    }

    // PATCH /barber/appointments/{id}/complete
    if ($method === 'PATCH' && preg_match('/^\/barber\/appointments\/(\d+)\/complete$/', $path, $matches)) {
        $appointmentId = $matches[1];
        
        // Verify the code before completing (only for online bookings)
        $data = json_decode(file_get_contents('php://input'), true);
        $providedCode = $data['verification_code'] ?? '';
        
        $stmt = $pdo->prepare("SELECT customer_id, status, verification_code, booking_type FROM appointments WHERE id = ? AND barber_id = ?");
        $stmt->execute([$appointmentId, $barberId]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$appointment) {
            http_response_code(404); echo json_encode(['error' => 'Booking not found or not yours']); exit;
        }
        
        // Walk-in bookings skip verification code check entirely
        $isWalkIn = ($appointment['booking_type'] === 'walk_in');
        if (!$isWalkIn && !empty($appointment['verification_code']) && strtoupper(trim($appointment['verification_code'])) !== strtoupper(trim($providedCode))) {
            http_response_code(400); echo json_encode(['error' => 'Invalid verification code provided. Please ask the customer for their code.']); exit;
        }
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?");
            $stmt->execute([$appointmentId]);
            
            // Notify customer
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message, related_entity_id) VALUES (?, 'customer', ?, 'booking', 'Booking Completed', 'Thank you! Your booking has been marked as completed.', ?)");
            $stmt->execute([$userId, $appointment['customer_id'], $appointmentId]);
            
            // Notify admin
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, type, title, message, related_entity_id) VALUES (?, 'admin', 'booking', 'Booking Completed by Barber', 'Barber has marked booking as completed.', ?)");
            $stmt->execute([$userId, $appointmentId]);
            
            // Audit Log
            $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'complete_booking', 'appointment', ?, ?)");
            $stmt->execute([$userId, $appointmentId, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Booking marked as completed']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500); echo json_encode(['error' => 'Failed to complete: ' . $e->getMessage()]);
        }
        exit;
    }
    
    // PATCH /barber/appointments/{id}/no-show
    if ($method === 'PATCH' && preg_match('/^\/barber\/appointments\/(\d+)\/no-show$/', $path, $matches)) {
        $appointmentId = $matches[1];
        
        // Ensure this appointment belongs to the barber
        $stmt = $pdo->prepare("SELECT customer_id, status FROM appointments WHERE id = ? AND barber_id = ?");
        $stmt->execute([$appointmentId, $barberId]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$appointment) {
            http_response_code(404); echo json_encode(['error' => 'Booking not found or not yours']); exit;
        }
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'no_show' WHERE id = ?");
            $stmt->execute([$appointmentId]);
            
            // Notify customer
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message, related_entity_id) VALUES (?, 'customer', ?, 'booking', 'Appointment Missed', 'Your appointment was marked as a no-show.', ?)");
            $stmt->execute([$userId, $appointment['customer_id'], $appointmentId]);
            
            // Notify admin
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, type, title, message, related_entity_id) VALUES (?, 'admin', 'booking', 'Booking No-Show by Barber', 'Barber has marked booking as no-show.', ?)");
            $stmt->execute([$userId, $appointmentId]);
            
            // Audit Log
            $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'no_show_booking', 'appointment', ?, ?)");
            $stmt->execute([$userId, $appointmentId, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Booking marked as no-show']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500); echo json_encode(['error' => 'Failed to mark as no-show: ' . $e->getMessage()]);
        }
        exit;
    }

    // POST /barber/appointments/walk-in
    if ($method === 'POST' && $path === '/barber/appointments/walk-in') {
        $data = json_decode(file_get_contents('php://input'), true);
        $customer_name = trim($data['customer_name'] ?? '');
        $customer_phone = trim($data['customer_phone'] ?? '');
        $customer_email = trim($data['customer_email'] ?? '');
        $service_id = $data['service_id'] ?? null;
        $appointment_date = $data['appointment_date'] ?? date('Y-m-d');
        $appointment_time = $data['appointment_time'] ?? date('H:i:s');
        $payment_method = $data['payment_method'] ?? 'cash'; // 'cash', 'pos', 'pending'

        if (!$customer_name || !$customer_phone || !$service_id) {
            http_response_code(400); echo json_encode(['error' => 'Customer name, phone, and service are required']); exit;
        }

        // Fetch service price
        $stmt = $pdo->prepare("SELECT price FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
        $service_price = $stmt->fetchColumn();
        if (!$service_price) {
            http_response_code(400); echo json_encode(['error' => 'Invalid service selected']); exit;
        }

        $pdo->beginTransaction();
        try {
            // Check if user exists by phone or email
            $customerId = null;
            if ($customer_email) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
                $stmt->execute([$customer_email, $customer_phone]);
                $customerId = $stmt->fetchColumn();
            } else {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
                $stmt->execute([$customer_phone]);
                $customerId = $stmt->fetchColumn();
            }

            if (!$customerId) {
                // Create dummy email if not provided
                if (!$customer_email) {
                    $customer_email = 'walkin_' . time() . '_' . rand(1000, 9999) . '@candycutz.local';
                }
                $hashedPassword = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT); // random pass
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'customer')");
                $stmt->execute([$customer_name, $customer_email, $customer_phone, $hashedPassword]);
                $customerId = $pdo->lastInsertId();
            }

            // Create appointment
            $stmt = $pdo->prepare("INSERT INTO appointments (customer_id, barber_id, service_id, appointment_date, appointment_time, status, booking_type) VALUES (?, ?, ?, ?, ?, 'confirmed', 'walk_in')");
            $stmt->execute([$customerId, $barberId, $service_id, $appointment_date, $appointment_time]);
            $appointmentId = $pdo->lastInsertId();

            // Create payment
            $payment_status = 'successful';
            if ($payment_method === 'pending') {
                $payment_status = 'pending';
            } elseif ($payment_method === 'transfer' || $payment_method === 'pos') {
                $payment_status = 'awaiting_verification';
            }
            $stmt = $pdo->prepare("INSERT INTO payments (customer_id, appointment_id, amount, status, payment_method) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$customerId, $appointmentId, $service_price, $payment_status, $payment_method]);

            $pdo->commit();

            // Notify admin about the walk-in
            try {
                $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, type, title, message, related_entity_id) VALUES (?, 'admin', 'booking', 'New Walk-In Customer', ?, ?)");
                $walkInMsg = "Walk-in customer '{$customer_name}' registered by barber. Payment: {$payment_method}.";
                $stmt->execute([$userId, $walkInMsg, $appointmentId]);
            } catch (PDOException $e) {
                // Ignore notification errors
            }

            echo json_encode(['success' => true, 'message' => 'Walk-in appointment created successfully']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500); echo json_encode(['error' => 'Failed to create walk-in appointment: ' . $e->getMessage()]);
        }
        exit;
    }
}

