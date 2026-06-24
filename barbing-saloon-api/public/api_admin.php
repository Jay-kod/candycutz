<?php
// Admin API Routes

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

// Ensure user is admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$role = $stmt->fetchColumn();

if ($role !== 'admin' && $role !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized: Admin access required']);
    exit;
}

// ==========================================
// DASHBOARD
// ==========================================
if ($method === 'GET' && $path === '/admin/dashboard') {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $weekAgo = date('Y-m-d', strtotime('-7 days'));
    $monthAgo = date('Y-m-d', strtotime('-30 days'));

    // --- Core KPIs ---
    // Appointments Today
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ?");
    $stmt->execute([$today]);
    $appointmentsToday = (int)$stmt->fetchColumn();

    // Yesterday's appointments for comparison
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ?");
    $stmt->execute([$yesterday]);
    $appointmentsYesterday = (int)$stmt->fetchColumn();

    // Pending Appointments
    $stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'");
    $pendingAppointments = (int)$stmt->fetchColumn();

    // Active Services
    $stmt = $pdo->query("SELECT COUNT(*) FROM services WHERE is_available = 1");
    $activeServices = (int)$stmt->fetchColumn();

    // Registered Barbers
    $stmt = $pdo->query("SELECT COUNT(*) FROM barbers");
    $totalBarbers = (int)$stmt->fetchColumn();

    // Total Customers
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    $totalCustomers = (int)$stmt->fetchColumn();

    // New customers this week
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'customer' AND created_at >= ?");
    $stmt->execute([$weekAgo]);
    $newCustomersWeek = (int)$stmt->fetchColumn();

    // --- Revenue ---
    // Total revenue (all time)
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'successful'");
    $totalRevenue = (float)$stmt->fetchColumn();

    // Revenue this month
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'successful' AND created_at >= ?");
    $stmt->execute([$monthAgo]);
    $revenueMonth = (float)$stmt->fetchColumn();

    // Revenue this week
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'successful' AND created_at >= ?");
    $stmt->execute([$weekAgo]);
    $revenueWeek = (float)$stmt->fetchColumn();

    // Revenue today (from service prices of completed today)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(s.price), 0) FROM appointments a JOIN services s ON a.service_id = s.id WHERE a.appointment_date = ? AND a.status = 'completed'");
    $stmt->execute([$today]);
    $revenueToday = (float)$stmt->fetchColumn();

    // --- Completion rate ---
    $stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'completed'");
    $completedCount = (int)$stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM appointments");
    $totalAppointments = (int)$stmt->fetchColumn();
    $completionRate = $totalAppointments > 0 ? round(($completedCount / $totalAppointments) * 100, 1) : 0;

    // --- Status Breakdown ---
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status");
    $statusRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $statusBreakdown = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0];
    foreach ($statusRows as $s) {
        $statusBreakdown[$s['status']] = (int)$s['count'];
    }

    // --- Revenue Trend (last 7 days) ---
    $revenueTrend = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $label = date('D', strtotime("-$i days")); // Mon, Tue, etc.
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(s.price), 0) FROM appointments a JOIN services s ON a.service_id = s.id WHERE a.appointment_date = ? AND a.status = 'completed'");
        $stmt->execute([$d]);
        $revenueTrend[] = ['day' => $label, 'date' => $d, 'revenue' => (float)$stmt->fetchColumn()];
    }

    // --- Booking Trend (last 7 days) ---
    $bookingTrend = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $label = date('D', strtotime("-$i days"));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ?");
        $stmt->execute([$d]);
        $bookingTrend[] = ['day' => $label, 'date' => $d, 'count' => (int)$stmt->fetchColumn()];
    }

    // --- Top Barbers (by completed bookings) ---
    $stmt = $pdo->query("
        SELECT b.id, u.name, u.avatar, b.rating, COUNT(a.id) as bookings, COALESCE(SUM(s.price), 0) as revenue
        FROM barbers b
        JOIN users u ON b.user_id = u.id
        LEFT JOIN appointments a ON b.id = a.barber_id AND a.status = 'completed'
        LEFT JOIN services s ON a.service_id = s.id
        GROUP BY b.id
        ORDER BY bookings DESC
        LIMIT 5
    ");
    $topBarbers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Recent Appointments (last 10) ---
    $stmt = $pdo->query("
        SELECT a.id, a.appointment_date, a.appointment_time, a.status,
               u.name as customer_name, u.avatar as customer_avatar,
               s.name as service_name, s.price as service_price,
               bu.name as barber_name
        FROM appointments a
        LEFT JOIN users u ON a.customer_id = u.id
        LEFT JOIN services s ON a.service_id = s.id
        LEFT JOIN barbers b ON a.barber_id = b.id
        LEFT JOIN users bu ON b.user_id = bu.id
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $recentAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Recent Activity (audit logs) ---
    $stmt = $pdo->query("
        SELECT al.action, al.entity_type, al.created_at, u.name as user_name, u.role as user_role
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC
        LIMIT 8
    ");
    $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Top Services ---
    $stmt = $pdo->query("
        SELECT s.name, COUNT(a.id) as bookings, COALESCE(SUM(s.price), 0) as revenue
        FROM services s
        LEFT JOIN appointments a ON s.id = a.service_id AND a.status = 'completed'
        GROUP BY s.id
        ORDER BY bookings DESC
        LIMIT 5
    ");
    $topServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'data' => [
            'stats' => [
                'appointments_today' => $appointmentsToday,
                'appointments_yesterday' => $appointmentsYesterday,
                'pending_appointments' => $pendingAppointments,
                'services' => $activeServices,
                'barbers' => $totalBarbers,
                'total_customers' => $totalCustomers,
                'new_customers_week' => $newCustomersWeek,
                'total_appointments' => $totalAppointments,
                'completion_rate' => $completionRate
            ],
            'revenue' => [
                'total' => $totalRevenue,
                'month' => $revenueMonth,
                'week' => $revenueWeek,
                'today' => $revenueToday
            ],
            'status_breakdown' => $statusBreakdown,
            'revenue_trend' => $revenueTrend,
            'booking_trend' => $bookingTrend,
            'top_barbers' => $topBarbers,
            'top_services' => $topServices,
            'recent_appointments' => $recentAppointments,
            'recent_activity' => $recentActivity
        ]
    ]);
    exit;
}

// ==========================================
// ADMIN ANALYTICS
// ==========================================
if ($method === 'GET' && $path === '/admin/analytics') {
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
        'platform_stats' => [],
        'revenue_trends' => [],
        'booking_trends' => [],
        'status_breakdown' => [],
        'top_barbers' => [],
        'top_services' => [],
        'customer_growth' => []
    ];

    // Business Stats
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'successful' $df");
    $analytics['business_stats']['total_revenue'] = (float)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'completed' $df");
    $analytics['business_stats']['total_appointments'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer' $df");
    $analytics['business_stats']['new_customers'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts");
    $analytics['platform_stats']['total_blog_posts'] = (int)$stmt->fetchColumn();

    // Top Barbers
    $stmt = $pdo->query("
        SELECT b.id, u.name, b.rating, COUNT(a.id) as bookings, COALESCE(SUM(p.amount), 0) as revenue
        FROM barbers b
        JOIN users u ON b.user_id = u.id
        LEFT JOIN appointments a ON b.id = a.barber_id AND a.status = 'completed' $dfA
        LEFT JOIN payments p ON p.appointment_id = a.id AND p.status = 'successful'
        GROUP BY b.id
        ORDER BY revenue DESC, bookings DESC
        LIMIT 5
    ");
    $analytics['top_barbers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Top Services
    $stmt = $pdo->query("
        SELECT s.name, COUNT(a.id) as count, COALESCE(SUM(p.amount), 0) as revenue 
        FROM services s
        LEFT JOIN appointments a ON s.id = a.service_id AND a.status = 'completed' $dfA
        LEFT JOIN payments p ON p.appointment_id = a.id AND p.status = 'successful'
        GROUP BY s.id 
        ORDER BY count DESC 
        LIMIT 5
    ");
    $analytics['top_services'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Status Breakdown
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM appointments WHERE 1=1 $df GROUP BY status");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $breakdown = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0];
    foreach ($statuses as $s) {
        $breakdown[$s['status']] = (int)$s['count'];
    }
    $analytics['status_breakdown'] = $breakdown;

    // Revenue Trends (last 6 months)
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(a.appointment_date, '%b') as month, COALESCE(SUM(p.amount), 0) as revenue 
        FROM appointments a
        LEFT JOIN payments p ON p.appointment_id = a.id AND p.status = 'successful'
        WHERE a.appointment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH) AND a.status = 'completed'
        GROUP BY YEAR(a.appointment_date), MONTH(a.appointment_date), month
        ORDER BY YEAR(a.appointment_date) ASC, MONTH(a.appointment_date) ASC
    ");
    $analytics['revenue_trends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Booking Trends (last 6 months)
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(appointment_date, '%b') as month, COUNT(*) as count 
        FROM appointments
        WHERE appointment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        GROUP BY YEAR(appointment_date), MONTH(appointment_date), month
        ORDER BY YEAR(appointment_date) ASC, MONTH(appointment_date) ASC
    ");
    $analytics['booking_trends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Customer Growth (last 6 months)
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%b') as month, COUNT(*) as count 
        FROM users
        WHERE role = 'customer' AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        GROUP BY YEAR(created_at), MONTH(created_at), month
        ORDER BY YEAR(created_at) ASC, MONTH(created_at) ASC
    ");
    $analytics['customer_growth'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $analytics]);
    exit;
}

// ==========================================
// APPOINTMENTS MANAGEMENT
// ==========================================
if (strpos($path, '/admin/appointments') === 0) {
    if ($method === 'GET' && $path === '/admin/appointments') {
        $stmt = $pdo->query("
            SELECT a.*, 
                   u.name as client_name, u.email as client_email, u.phone as client_phone,
                   s.name as service_name, s.price as service_price, s.duration_minutes,
                   b.user_id as barber_user_id, bu.name as barber_name,
                   p.status as payment_status, p.receipt_image, p.transaction_ref, p.verified_by, p.payment_method
            FROM appointments a
            LEFT JOIN users u ON a.customer_id = u.id
            LEFT JOIN services s ON a.service_id = s.id
            LEFT JOIN barbers b ON a.barber_id = b.id
            LEFT JOIN users bu ON b.user_id = bu.id
            LEFT JOIN payments p ON a.id = p.appointment_id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ");
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($appointments as &$appt) {
            $appt['service'] = ['name' => $appt['service_name'], 'price' => $appt['service_price'], 'duration_minutes' => $appt['duration_minutes']];
            $appt['barber'] = ['name' => $appt['barber_name']];
            $appt['customer'] = ['name' => $appt['client_name'], 'email' => $appt['client_email'], 'phone' => $appt['client_phone']];
            unset($appt['service_name'], $appt['service_price'], $appt['duration_minutes'], $appt['barber_name'], $appt['client_name'], $appt['client_email'], $appt['client_phone']);
        }
        echo json_encode(['data' => $appointments]);
        exit;
    }

    if ($method === 'PATCH' && preg_match('/^\/admin\/appointments\/(\d+)\/approve$/', $path, $matches)) {
        $appointmentId = $matches[1];
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'confirmed' WHERE id = ?");
            $stmt->execute([$appointmentId]);
            
            $stmt = $pdo->prepare("UPDATE payments SET status = 'completed', verified_by = 'admin' WHERE appointment_id = ?");
            $stmt->execute([$appointmentId]);
            
            $stmt = $pdo->prepare("SELECT customer_id FROM appointments WHERE id = ?");
            $stmt->execute([$appointmentId]);
            $customerId = $stmt->fetchColumn();
            
            if ($customerId) {
                $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message, related_entity_id) VALUES (?, 'customer', ?, 'booking', 'Booking Confirmed', 'Your booking and payment have been confirmed by the admin.', ?)");
                $stmt->execute([$userId, $customerId, $appointmentId]);
            }
            
            $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'approve_booking', 'appointment', ?, ?)");
            $stmt->execute([$userId, $appointmentId, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Appointment approved']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500); echo json_encode(['error' => 'Failed to approve']);
        }
        exit;
    }

    if ($method === 'PATCH' && preg_match('/^\/admin\/appointments\/(\d+)\/cancel$/', $path, $matches)) {
        $appointmentId = $matches[1];
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$appointmentId]);
        
        $stmt = $pdo->prepare("UPDATE payments SET status = 'failed' WHERE appointment_id = ?");
        $stmt->execute([$appointmentId]);
        
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'cancel_booking', 'appointment', ?, ?)");
        $stmt->execute([$userId, $appointmentId, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
        
        echo json_encode(['success' => true, 'message' => 'Appointment cancelled']);
        exit;
    }

    // POST /admin/appointments/walk-in
    if ($method === 'POST' && $path === '/admin/appointments/walk-in') {
        $data = json_decode(file_get_contents('php://input'), true);
        $customer_name = trim($data['customer_name'] ?? '');
        $customer_phone = trim($data['customer_phone'] ?? '');
        $customer_email = trim($data['customer_email'] ?? '');
        $barber_id = $data['barber_id'] ?? null;
        $service_id = $data['service_id'] ?? null;
        $appointment_date = $data['appointment_date'] ?? date('Y-m-d');
        $appointment_time = $data['appointment_time'] ?? date('H:i:s');
        $payment_method = $data['payment_method'] ?? 'cash';

        if (!$customer_name || !$customer_phone || !$service_id || !$barber_id) {
            http_response_code(400); echo json_encode(['error' => 'Customer name, phone, barber, and service are required']); exit;
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
                if (!$customer_email) {
                    $customer_email = 'walkin_' . time() . '_' . rand(1000, 9999) . '@candycutz.local';
                }
                $hashedPassword = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'customer')");
                $stmt->execute([$customer_name, $customer_email, $customer_phone, $hashedPassword]);
                $customerId = $pdo->lastInsertId();
            }

            // Create appointment
            $stmt = $pdo->prepare("INSERT INTO appointments (customer_id, barber_id, service_id, appointment_date, appointment_time, status, booking_type) VALUES (?, ?, ?, ?, ?, 'confirmed', 'walk_in')");
            $stmt->execute([$customerId, $barber_id, $service_id, $appointment_date, $appointment_time]);
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

            // Audit log
            $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'create_walkin', 'appointment', ?, ?)");
            $stmt->execute([$userId, $appointmentId, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Walk-in appointment created successfully']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500); echo json_encode(['error' => 'Failed to create walk-in appointment: ' . $e->getMessage()]);
        }
        exit;
    }
}

// ==========================================
// SETTINGS MANAGEMENT
// ==========================================
if ($method === 'GET' && $path === '/admin/settings') {
    $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['key']] = $row['value'];
    }
    echo json_encode(['data' => $settings]);
    exit;
}

if ($method === 'POST' && $path === '/admin/settings') {
    // Handle form-data since frontend sends FormData (for image uploads)
    $settingsData = isset($_POST['settings']) && is_array($_POST['settings']) ? $_POST['settings'] : [];
    
    // Handle image upload if present
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['hero_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (in_array($file['type'], $allowedTypes)) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'hero_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $uploadDir = __DIR__ . '/storage/hero/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                $settingsData['hero_image'] = 'hero/' . $filename;
            }
        }
    }
    
    // Handle about image upload if present
    if (isset($_FILES['about_teaser_image']) && $_FILES['about_teaser_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['about_teaser_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (in_array($file['type'], $allowedTypes)) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'about_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $uploadDir = __DIR__ . '/storage/about/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                $settingsData['about_teaser_image'] = 'about/' . $filename;
            }
        }
    }
    
    // Handle about shop image upload if present
    if (isset($_FILES['about_shop_image']) && $_FILES['about_shop_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['about_shop_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (in_array($file['type'], $allowedTypes)) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'about_shop_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $uploadDir = __DIR__ . '/storage/about/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                $settingsData['about_shop_image'] = 'about/' . $filename;
            }
        }
    }

    if (empty($settingsData)) {
        http_response_code(400); echo json_encode(['error' => 'No data provided']); exit;
    }
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
        foreach ($settingsData as $key => $value) {
            $stmt->execute([$key, $value, $value]);
        }
        
        $logStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, ip_address) VALUES (?, 'update_settings', 'system', ?)");
        $logStmt->execute([$userId, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
        
        $pdo->commit();
        
        // Return the updated settings so the frontend can refresh the UI
        $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
        $allSettings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $allSettings[$row['key']] = $row['value'];
        }
        echo json_encode(['success' => true, 'message' => 'Settings updated successfully', 'data' => $allSettings]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500); echo json_encode(['error' => 'Failed to update settings: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// SERVICES MANAGEMENT
// ==========================================
if (strpos($path, '/admin/services') === 0) {
    // GET all global services
    if ($method === 'GET' && $path === '/admin/services') {
        $stmt = $pdo->query("SELECT s.*, c.name as category_name FROM services s LEFT JOIN service_categories c ON s.category_id = c.id ORDER BY s.name ASC");
        echo json_encode(['data' => $stmt->fetchAll()]);
        exit;
    }

    // Helper to handle image uploads
    $handleUpload = function($fileKey) {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$fileKey];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) return null;
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'service_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $uploadDir = __DIR__ . '/uploads/services/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                return '/public/uploads/services/' . $filename;
            }
        }
        return null;
    };

    // POST create new service
    if ($method === 'POST' && $path === '/admin/services') {
        $data = strpos($_SERVER["CONTENT_TYPE"] ?? '', 'multipart/form-data') !== false ? $_POST : json_decode(file_get_contents('php://input'), true);
        
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');
        $price = floatval($data['price'] ?? 0);
        $duration = intval($data['duration_minutes'] ?? 30);
        $categoryId = !empty($data['category_id']) && $data['category_id'] !== 'null' ? intval($data['category_id']) : null;
        $isAvailable = isset($data['is_available']) ? (in_array($data['is_available'], [1, '1', 'true', true], true) ? 1 : 0) : 1;

        if (!$name || $price <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'Name and a valid price are required']);
            exit;
        }

        $image1 = $handleUpload('image1');
        $image2 = $handleUpload('image2');
        $image3 = $handleUpload('image3');

        $stmt = $pdo->prepare("INSERT INTO services (name, description, price, duration_minutes, category_id, is_available, image, image2, image3, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $description, $price, $duration, $categoryId, $isAvailable, $image1, $image2, $image3]);
        echo json_encode(['message' => 'Service created', 'id' => $pdo->lastInsertId()]);
        exit;
    }

    // POST (simulated PUT via FormData) update service
    if ($method === 'POST' && preg_match('#^/admin/services/(\d+)$#', $path, $m)) {
        $serviceId = $m[1];
        $data = strpos($_SERVER["CONTENT_TYPE"] ?? '', 'multipart/form-data') !== false ? $_POST : json_decode(file_get_contents('php://input'), true);
        
        // Ensure service exists
        $stmt = $pdo->prepare("SELECT image, image2, image3 FROM services WHERE id = ?");
        $stmt->execute([$serviceId]);
        $existing = $stmt->fetch();
        if (!$existing) {
            http_response_code(404); echo json_encode(['error' => 'Service not found']); exit;
        }

        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');
        $price = floatval($data['price'] ?? 0);
        $duration = intval($data['duration_minutes'] ?? 30);
        $categoryId = !empty($data['category_id']) && $data['category_id'] !== 'null' ? intval($data['category_id']) : null;
        $isAvailable = isset($data['is_available']) ? (in_array($data['is_available'], [1, '1', 'true', true], true) ? 1 : 0) : 1;

        if (!$name || $price <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'Name and a valid price are required']);
            exit;
        }

        $image1 = $handleUpload('image1') ?? $existing['image'];
        $image2 = $handleUpload('image2') ?? $existing['image2'];
        $image3 = $handleUpload('image3') ?? $existing['image3'];
        
        // Handle image removal if specified
        if (isset($data['remove_image1']) && $data['remove_image1'] === 'true') $image1 = null;
        if (isset($data['remove_image2']) && $data['remove_image2'] === 'true') $image2 = null;
        if (isset($data['remove_image3']) && $data['remove_image3'] === 'true') $image3 = null;

        $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ?, price = ?, duration_minutes = ?, category_id = ?, is_available = ?, image = ?, image2 = ?, image3 = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $duration, $categoryId, $isAvailable, $image1, $image2, $image3, $serviceId]);
        echo json_encode(['message' => 'Service updated']);
        exit;
    }
    
    // Fallback standard PUT (without images) for backward compatibility
    if ($method === 'PUT' && preg_match('#^/admin/services/(\d+)$#', $path, $m)) {
        // ... handled same as above without files. But let's let the POST simulated PUT do the heavy lifting from now on.
        // Actually, just keep it simple: if Axios sends FormData for PUT, PHP won't populate $_POST. 
        // We MUST use POST with `_method=PUT` or just use POST to an update endpoint.
        // The regex `^/admin/services/(\d+)$` with POST is perfect. I will change admin.api.js to use POST.
        $serviceId = $m[1];
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');
        $price = floatval($data['price'] ?? 0);
        $duration = intval($data['duration_minutes'] ?? 30);
        $categoryId = !empty($data['category_id']) && $data['category_id'] !== 'null' ? intval($data['category_id']) : null;
        $isAvailable = isset($data['is_available']) ? (in_array($data['is_available'], [1, '1', 'true', true], true) ? 1 : 0) : 1;

        if (!$name || $price <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'Name and a valid price are required']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ?, price = ?, duration_minutes = ?, category_id = ?, is_available = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $duration, $categoryId, $isAvailable, $serviceId]);
        echo json_encode(['message' => 'Service updated']);
        exit;
    }

    // DELETE service
    if ($method === 'DELETE' && preg_match('#^/admin/services/(\d+)$#', $path, $m)) {
        $serviceId = $m[1];
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$serviceId]);
        echo json_encode(['message' => 'Service deleted']);
        exit;
    }
}

// ==========================================
// GALLERY MANAGEMENT
// ==========================================
if (strpos($path, '/admin/gallery') === 0) {
    // GET all global gallery items
    if ($method === 'GET' && $path === '/admin/gallery') {
        $stmt = $pdo->query("SELECT g.*, u.name as barber_name FROM gallery g LEFT JOIN barbers b ON g.barber_id = b.id LEFT JOIN users u ON b.user_id = u.id ORDER BY g.created_at DESC");
        echo json_encode(['data' => $stmt->fetchAll()]);
        exit;
    }

    // POST add gallery image
    if ($method === 'POST' && $path === '/admin/gallery') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? '';
        $barber_id = !empty($_POST['barber_id']) ? $_POST['barber_id'] : null;
        $image_url = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/gallery/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'admin_gallery_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $image_url = '/uploads/gallery/' . $filename;
            } else {
                http_response_code(500); echo json_encode(['error' => 'Failed to move uploaded file']); exit;
            }
        } else {
            $image_url = $_POST['image_url'] ?? '';
        }

        if (!$image_url) {
            http_response_code(400); echo json_encode(['error' => 'Image file required']); exit;
        }

        $stmt = $pdo->prepare("INSERT INTO gallery (barber_id, image_path, title, description, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$barber_id, $image_url, $title, $description, $category]);
        echo json_encode(['success' => true, 'message' => 'Image added to gallery']);
        exit;
    }

    // POST update gallery image
    if ($method === 'POST' && preg_match('#^/admin/gallery/(\d+)$#', $path, $matches)) {
        $id = $matches[1];
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? '';
        $barber_id = !empty($_POST['barber_id']) ? $_POST['barber_id'] : null;
        $image_url = $_POST['image_url'] ?? '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/gallery/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'admin_gallery_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $image_url = '/uploads/gallery/' . $filename;
            }
        }

        if ($image_url) {
            $stmt = $pdo->prepare("UPDATE gallery SET barber_id = ?, title = ?, description = ?, category = ?, image_path = ? WHERE id = ?");
            $stmt->execute([$barber_id, $title, $description, $category, $image_url, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE gallery SET barber_id = ?, title = ?, description = ?, category = ? WHERE id = ?");
            $stmt->execute([$barber_id, $title, $description, $category, $id]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Gallery image updated']);
        exit;
    }

    // DELETE gallery image
    if ($method === 'DELETE' && preg_match('#^/admin/gallery/(\d+)$#', $path, $matches)) {
        $id = $matches[1];
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Image deleted']);
        exit;
    }
}

// ==========================================
// BLOG MANAGEMENT
// ==========================================
if (strpos($path, '/admin/blog') === 0) {
    // GET all global blog posts
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT b.*, u.name as author_name FROM blog_posts b LEFT JOIN users u ON b.author_id = u.id ORDER BY b.created_at DESC");
        echo json_encode(['data' => $stmt->fetchAll()]);
        exit;
    }

    // UPDATE - must be checked before CREATE since POST is used for both
    if (($method === 'PUT' || $method === 'POST') && preg_match('/^\/admin\/blog\/(\d+)$/', $path, $matches)) {
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
                $filename = 'blog_admin_' . time() . '_' . uniqid() . '.' . $ext;
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

    // CREATE - only matches exact /admin/blog path
    if ($method === 'POST' && $path === '/admin/blog') {
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
                $filename = 'blog_admin_' . time() . '_' . uniqid() . '.' . $ext;
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
        
        // Use $userId for author_id since admin is also a user
        $stmt = $pdo->prepare("INSERT INTO blog_posts (author_id, title, slug, content, excerpt, is_published, featured_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $slug, $content, $excerpt, $is_published, $featured_image]);
        
        if ($is_published) {
            $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message) VALUES (?, 'all_customers', NULL, 'blog_update', 'New Blog Post', ?)");
            $stmt->execute([$userId, "A new blog post '$title' has been published!"]);
        }

        echo json_encode(['success' => true, 'message' => 'Blog post created']);
        exit;
    }

    // DELETE
    if ($method === 'DELETE' && preg_match('/^\/admin\/blog\/(\d+)$/', $path, $matches)) {
        $id = $matches[1];
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Blog post deleted']);
        exit;
    }
}

// ==========================================
// SERVICE CATEGORIES
// ==========================================
if (strpos($path, '/admin/service-categories') === 0) {
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT * FROM service_categories ORDER BY name ASC");
        echo json_encode(['data' => $stmt->fetchAll()]);
        exit;
    }
}

// ==========================================
// BARBERS MANAGEMENT
// ==========================================
if (strpos($path, '/admin/barbers') === 0) {
    if ($method === 'GET' && $path === '/admin/barbers') {
        $stmt = $pdo->query("SELECT b.*, u.name, u.email, u.avatar, u.phone, u.is_active 
                             FROM barbers b 
                             INNER JOIN users u ON b.user_id = u.id 
                             ORDER BY u.name ASC");
        $barbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($barbers as &$barber) {
            $barber['specialties'] = json_decode($barber['specialties'], true) ?: [];
        }
        echo json_encode(['data' => $barbers]);
        exit;
    }
    
    if ($method === 'POST' && $path === '/admin/barbers') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $bio = $data['bio'] ?? '';
        $experience = $data['experience_years'] ?? 0;
        $specialties = json_encode(array_map('trim', explode(',', $data['specialties'] ?? '')));
        
        if (!$name || !$email || !$password) {
            http_response_code(400); echo json_encode(['error' => 'Name, email, and password are required']); exit;
        }
        
        try {
            $pdo->beginTransaction();
            
            // 1. Create User
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'barber')");
            $stmt->execute([$name, $email, $hashedPassword]);
            $newUserId = $pdo->lastInsertId();
            
            // 2. Create Barber (new barbers start as pending_approval)
            $status = $data['status'] ?? 'pending_approval';
            $stmt = $pdo->prepare("INSERT INTO barbers (user_id, bio, specialties, experience_years, is_available, status) VALUES (?, ?, ?, ?, 1, ?)");
            $stmt->execute([$newUserId, $bio, $specialties, $experience, $status]);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Barber created successfully']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            // Handle duplicate email error code (23000 usually)
            if ($e->getCode() == 23000) {
                http_response_code(409); echo json_encode(['error' => 'Email already exists']);
            } else {
                http_response_code(500); echo json_encode(['error' => 'Database error']);
            }
        }
        exit;
    }
    
    if ($method === 'PUT' && preg_match('/^\/admin\/barbers\/(\d+)$/', $path, $matches)) {
        $barberId = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? ''; // Optional on edit
        $bio = $data['bio'] ?? '';
        $experience = $data['experience_years'] ?? 0;
        
        // Handle specialties array/string conversion securely
        if (is_array($data['specialties'])) {
            $specialties = json_encode($data['specialties']);
        } else {
            $specialties = json_encode(array_map('trim', explode(',', $data['specialties'] ?? '')));
        }
        
        if (!$name || !$email) {
            http_response_code(400); echo json_encode(['error' => 'Name and email are required']); exit;
        }
        
        try {
            // Get user_id first
            $stmt = $pdo->prepare("SELECT user_id FROM barbers WHERE id = ?");
            $stmt->execute([$barberId]);
            $barberUserId = $stmt->fetchColumn();
            
            if (!$barberUserId) {
                http_response_code(404); echo json_encode(['error' => 'Barber not found']); exit;
            }
            
            $pdo->beginTransaction();
            
            // 1. Update User
            if ($password) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $email, $hashedPassword, $barberUserId]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->execute([$name, $email, $barberUserId]);
            }
            
            // 2. Update Barber
            $status = $data['status'] ?? 'active';
            $stmt = $pdo->prepare("UPDATE barbers SET bio = ?, specialties = ?, experience_years = ?, status = ? WHERE id = ?");
            $stmt->execute([$bio, $specialties, $experience, $status, $barberId]);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Barber updated successfully']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                http_response_code(409); echo json_encode(['error' => 'Email already exists']);
            } else {
                http_response_code(500); echo json_encode(['error' => 'Database error']);
            }
        }
        exit;
    }
    
    // Quick status change
    if ($method === 'PATCH' && preg_match('/^\/admin\/barbers\/(\d+)\/status$/', $path, $matches)) {
        $barberId = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        $status = $data['status'] ?? '';
        
        $allowed = ['active', 'pending_approval', 'suspended', 'on_leave'];
        if (!in_array($status, $allowed)) {
            http_response_code(400); echo json_encode(['error' => 'Invalid status']); exit;
        }
        
        $stmt = $pdo->prepare("UPDATE barbers SET status = ? WHERE id = ?");
        $stmt->execute([$status, $barberId]);
        echo json_encode(['success' => true, 'message' => 'Status updated']);
        exit;
    }
    
    if ($method === 'DELETE' && preg_match('/^\/admin\/barbers\/(\d+)$/', $path, $matches)) {
        $barberId = $matches[1];
        
        try {
            // Get user_id first
            $stmt = $pdo->prepare("SELECT user_id FROM barbers WHERE id = ?");
            $stmt->execute([$barberId]);
            $barberUserId = $stmt->fetchColumn();
            
            if (!$barberUserId) {
                http_response_code(404); echo json_encode(['error' => 'Barber not found']); exit;
            }
            
            $pdo->beginTransaction();
            
            // Clean up all dependent records referencing barbers.id
            $pdo->prepare("DELETE FROM appointments WHERE barber_id = ?")->execute([$barberId]);
            $pdo->prepare("DELETE FROM gallery WHERE barber_id = ?")->execute([$barberId]);
            $pdo->prepare("UPDATE services SET barber_id = NULL WHERE barber_id = ?")->execute([$barberId]);
            $pdo->prepare("DELETE FROM testimonials WHERE barber_id = ?")->execute([$barberId]);
            $pdo->prepare("DELETE FROM working_hours WHERE barber_id = ?")->execute([$barberId]);
            $pdo->prepare("DELETE FROM holidays WHERE barber_id = ?")->execute([$barberId]);
            
            // Clean up records referencing users.id
            $pdo->prepare("DELETE FROM blog_reactions WHERE customer_id = ?")->execute([$barberUserId]);
            $pdo->prepare("DELETE FROM blog_posts WHERE author_id = ?")->execute([$barberUserId]);
            $pdo->prepare("DELETE FROM audit_logs WHERE user_id = ?")->execute([$barberUserId]);
            
            // Delete barber record
            $stmt = $pdo->prepare("DELETE FROM barbers WHERE id = ?");
            $stmt->execute([$barberId]);
            
            // Delete user record
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$barberUserId]);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Barber deleted successfully']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500); echo json_encode(['error' => 'Failed to delete barber: ' . $e->getMessage()]);
        }
        exit;
    }
}

// ==========================================
// SYSTEM AUDIT LOGS
// ==========================================
if ($method === 'GET' && $path === '/admin/logs') {
    $stmt = $pdo->query("
        SELECT a.*, u.name as user_name, u.email as user_email, u.role as user_role
        FROM audit_logs a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 100
    ");
    echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

// ==========================================
// CUSTOMERS
// ==========================================
if ($method === 'GET' && $path === '/admin/customers') {
    $stmt = $pdo->query("
        SELECT 
            u.id, 
            u.name, 
            u.email, 
            u.phone, 
            u.avatar, 
            u.created_at,
            (SELECT COUNT(*) FROM appointments WHERE customer_id = u.id) as total_bookings,
            (SELECT COUNT(*) FROM appointments WHERE customer_id = u.id AND status = 'no_show') as no_shows,
            (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE appointment_id IN (SELECT id FROM appointments WHERE customer_id = u.id) AND status = 'successful') as total_spent,
            (SELECT appointment_date FROM appointments WHERE customer_id = u.id ORDER BY appointment_date DESC LIMIT 1) as last_visit
        FROM users u
        WHERE u.role = 'customer'
        ORDER BY u.created_at DESC
    ");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['data' => $customers]);
    exit;
}

// ==========================================
// VERIFICATIONS
// ==========================================
if (strpos($path, '/admin/verifications') === 0) {
    if ($method === 'GET' && $path === '/admin/verifications/stats') {
        // Total codes generated
        $stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE verification_code IS NOT NULL AND verification_code != ''");
        $totalCodes = (int)$stmt->fetchColumn();

        // Verified today
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE verification_code IS NOT NULL AND verification_code != '' AND status = 'completed' AND DATE(updated_at) = ?");
        $stmt->execute([date('Y-m-d')]);
        $verifiedToday = (int)$stmt->fetchColumn();

        // Pending verification
        $stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE verification_code IS NOT NULL AND verification_code != '' AND status = 'confirmed'");
        $pendingVerification = (int)$stmt->fetchColumn();

        // Expired/missed (confirmed but past date)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE verification_code IS NOT NULL AND verification_code != '' AND status = 'confirmed' AND appointment_date < ?");
        $stmt->execute([date('Y-m-d')]);
        $expired = (int)$stmt->fetchColumn();

        echo json_encode(['data' => [
            'total' => $totalCodes,
            'verified_today' => $verifiedToday,
            'pending' => $pendingVerification,
            'expired' => $expired
        ]]);
        exit;
    }

    if ($method === 'GET' && $path === '/admin/verifications') {
        $filter = $_GET['filter'] ?? 'all';
        $search = $_GET['search'] ?? '';

        $query = "
            SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.verification_code, 
                   s.name as service_name, b.name as barber_name, c.name as customer_name, c.avatar as customer_avatar
            FROM appointments a
            LEFT JOIN services s ON a.service_id = s.id
            LEFT JOIN barbers b_model ON a.barber_id = b_model.id
            LEFT JOIN users b ON b_model.user_id = b.id
            LEFT JOIN users c ON a.customer_id = c.id
            WHERE a.verification_code IS NOT NULL AND a.verification_code != ''
        ";

        $params = [];

        if ($filter === 'confirmed') {
            $query .= " AND a.status = 'confirmed'";
        } elseif ($filter === 'completed') {
            $query .= " AND a.status = 'completed'";
        } elseif ($filter === 'expired') {
            $query .= " AND a.status = 'confirmed' AND a.appointment_date < ?";
            $params[] = date('Y-m-d');
        }

        if ($search) {
            $query .= " AND (a.verification_code LIKE ? OR c.name LIKE ? OR b.name LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        $query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $verifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['data' => $verifications]);
        exit;
    }

    if ($method === 'PATCH' && preg_match('/^\/admin\/verifications\/(\d+)\/verify$/', $path, $matches)) {
        $appointmentId = $matches[1];
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = ? AND status = 'confirmed'");
            $stmt->execute([$appointmentId]);
            
            if ($stmt->rowCount() === 0) {
                http_response_code(400); echo json_encode(['error' => 'Appointment is not in a confirmable state.']); exit;
            }

            // Get customer_id to notify
            $stmt = $pdo->prepare("SELECT customer_id FROM appointments WHERE id = ?");
            $stmt->execute([$appointmentId]);
            $customerId = $stmt->fetchColumn();

            // Notify customer
            if ($customerId) {
                $stmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message, related_entity_id) VALUES (?, 'customer', ?, 'booking', 'Booking Verified by Admin', 'Your booking has been manually verified and marked as completed by an admin.', ?)");
                $stmt->execute([$userId, $customerId, $appointmentId]);
            }

            // Audit log
            $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'manual_verify_booking', 'appointment', ?, ?)");
            $stmt->execute([$userId, $appointmentId, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Appointment verified and marked as completed']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500); echo json_encode(['error' => 'Failed to verify appointment: ' . $e->getMessage()]);
        }
        exit;
    }
}
