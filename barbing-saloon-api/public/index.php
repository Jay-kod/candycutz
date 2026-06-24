<?php
require __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Parse .env for JWT_SECRET
$jwtSecret = 'candycutz_super_secret_jwt_key_2026';
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            if (trim($name) === 'JWT_SECRET') {
                $jwtSecret = trim($value);
                break;
            }
        }
    }
}
$jwtSecret = getenv('JWT_SECRET') ?: $jwtSecret;

// Serve static files (uploads) directly with proper MIME type
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('/^\/(uploads|storage)\//', $requestUri)) {
    $filePath = __DIR__ . $requestUri;
    if (file_exists($filePath) && is_file($filePath)) {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'svg' => 'image/svg+xml',
        ];
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=86400');
        readfile($filePath);
        exit;
    }
}

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');

// Database connection
$host = getenv('DB_HOST') ?: '127.0.0.1';
$db = getenv('DB_DATABASE') ?: 'candycutz_db';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Parse request path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('/^\/api/', '', $path);
$method = $_SERVER['REQUEST_METHOD'];

// API Routes
if ($method === 'GET') {
    // Public settings
    if ($path === '/public/settings') {
        $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['key']] = $row['value'];
        }
        echo json_encode(['data' => ($settings ?: ['name' => 'CandyCutz'])]);
        exit;
    }

    // Public services list
    if ($path === '/public/services') {
        $stmt = $pdo->query("
            SELECT s.id, s.name, s.description, s.image, s.image2, s.image3, s.price, s.duration_minutes, s.barber_id,
                   c.id as category_id, c.name as category_name,
                   u.name as barber_name, u.avatar as barber_avatar
            FROM services s
            LEFT JOIN service_categories c ON s.category_id = c.id
            LEFT JOIN barbers b ON s.barber_id = b.id
            LEFT JOIN users u ON b.user_id = u.id
            WHERE s.is_available = 1
        ");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format category and barber objects for frontend
        foreach ($services as &$service) {
            if ($service['category_name']) {
                $service['category'] = [
                    'id' => $service['category_id'],
                    'name' => $service['category_name']
                ];
            }
            $service['barber'] = [
                'id' => $service['barber_id'],
                'name' => $service['barber_name'],
                'avatar' => $service['barber_avatar']
            ];
            unset($service['category_id'], $service['category_name'], $service['barber_name'], $service['barber_avatar']);
        }
        
        echo json_encode(['data' => $services, 'count' => count($services)]);
        exit;
    }

    // Public barbers list
    if ($path === '/public/barbers') {
        $stmt = $pdo->query("SELECT b.id, u.name, u.avatar, b.bio, b.rating, b.experience_years as years_experience, b.specialties, b.status, (b.rating >= 4.8) as is_featured FROM barbers b 
                            JOIN users u ON b.user_id = u.id WHERE b.is_available = 1");
        $barbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decode JSON specialties
        foreach ($barbers as &$barber) {
            if (isset($barber['specialties'])) {
                $barber['specialties'] = json_decode($barber['specialties'], true);
            }
        }
        
        echo json_encode(['data' => $barbers, 'count' => count($barbers)]);
        exit;
    }

    // Single barber profile
    if (preg_match('/^\/public\/barbers\/(\d+)$/', $path, $matches)) {
        $id = $matches[1];
        $stmt = $pdo->prepare("SELECT b.id, u.name, u.avatar, b.bio, b.rating, b.experience_years as years_experience, b.specialties, b.status FROM barbers b 
                            JOIN users u ON b.user_id = u.id WHERE b.id = ? AND b.is_available = 1");
        $stmt->execute([$id]);
        $barber = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($barber) {
            if (isset($barber['specialties'])) {
                $barber['specialties'] = json_decode($barber['specialties'], true);
            }
            
            // Get barber's gallery
            $gStmt = $pdo->prepare("SELECT id, title, category, image_path, description, is_featured FROM gallery WHERE barber_id = ? ORDER BY is_featured DESC, created_at DESC");
            $gStmt->execute([$id]);
            $barber['gallery'] = $gStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['data' => $barber]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Barber not found']);
        }
        exit;
    }
    if ($path === '/public/gallery') {
        $stmt = $pdo->query("SELECT g.*, u.name as barber_name FROM gallery g LEFT JOIN barbers b ON g.barber_id = b.id LEFT JOIN users u ON b.user_id = u.id ORDER BY g.is_featured DESC, g.created_at DESC LIMIT 20");
        $gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['data' => $gallery, 'count' => count($gallery)]);
        exit;
    }

    // Public testimonials
    if ($path === '/public/testimonials') {
        $serviceId = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;
        
        $sql = "SELECT t.id, t.rating, t.comment, t.created_at, u.name as customer_name, b.id as barber_id, bu.name as barber_name 
                FROM testimonials t
                JOIN users u ON t.customer_id = u.id
                LEFT JOIN barbers b ON t.barber_id = b.id
                LEFT JOIN users bu ON b.user_id = bu.id";
                
        $params = [];
        if ($serviceId) {
            $sql .= " WHERE t.service_id = ?";
            $params[] = $serviceId;
        }
        
        $sql .= " ORDER BY t.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['data' => $testimonials, 'count' => count($testimonials)]);
        exit;
    }

    // Service categories
    if ($path === '/public/service-categories') {
        $stmt = $pdo->query("SELECT id, name, description, icon FROM service_categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['data' => $categories]);
        exit;
    }

    // Blog posts
    if ($path === '/public/blog') {
        $stmt = $pdo->query("SELECT p.id, p.title, p.slug, p.excerpt, p.featured_image, p.created_at, u.name as author_name,
            (SELECT SUM(CASE WHEN reaction_type = 'love' THEN 1 ELSE 0 END) FROM blog_reactions WHERE post_id = p.id) as loves_count,
            (SELECT SUM(CASE WHEN reaction_type = 'dislike' THEN 1 ELSE 0 END) FROM blog_reactions WHERE post_id = p.id) as dislikes_count
            FROM blog_posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.is_published = 1 ORDER BY p.created_at DESC");
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format author object for Vue component
        $formattedPosts = array_map(function($post) {
            $post['author'] = ['name' => $post['author_name']];
            $post['loves_count'] = (int)$post['loves_count'];
            $post['dislikes_count'] = (int)$post['dislikes_count'];
            unset($post['author_name']);
            return $post;
        }, $posts);
        
        echo json_encode(['data' => $formattedPosts]);
        exit;
    }

    // Single blog post
    if (preg_match('/^\/public\/blog\/([a-zA-Z0-9-]+)$/', $path, $matches)) {
        $slug = $matches[1];
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);
        $parts = explode('_', $token);
        $userId = isset($parts[2]) ? intval($parts[2]) : 0;
        
        $stmt = $pdo->prepare("SELECT p.id, p.title, p.slug, p.content, p.featured_image, p.created_at, u.name as author_name,
            (SELECT SUM(CASE WHEN reaction_type = 'love' THEN 1 ELSE 0 END) FROM blog_reactions WHERE post_id = p.id) as loves_count,
            (SELECT SUM(CASE WHEN reaction_type = 'dislike' THEN 1 ELSE 0 END) FROM blog_reactions WHERE post_id = p.id) as dislikes_count,
            (SELECT reaction_type FROM blog_reactions WHERE post_id = p.id AND customer_id = ?) as user_reaction
            FROM blog_posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.slug = ? AND p.is_published = 1");
        $stmt->execute([$userId, $slug]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($post) {
            $post['author'] = ['name' => $post['author_name']];
            $post['loves_count'] = (int)$post['loves_count'];
            $post['dislikes_count'] = (int)$post['dislikes_count'];
            unset($post['author_name']);
            echo json_encode(['data' => $post]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Post not found']);
        }
        exit;
    }

    // Working hours
    if ($path === '/public/working-hours') {
        $stmt = $pdo->query("SELECT barber_id, day_of_week, start_time, end_time FROM working_hours WHERE is_available = 1");
        $hours = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['data' => $hours]);
        exit;
    }

    // Available slots
    if ($path === '/public/available-slots') {
        $barber_id = $_GET['barber_id'] ?? null;
        $date = $_GET['date'] ?? null;
        
        if (!$barber_id || !$date) {
            echo json_encode(['data' => []]);
            exit;
        }

        // Get day of week (0 = Sunday, 1 = Monday...)
        $dayOfWeek = date('w', strtotime($date));

        // Get barber's working hours for that day
        $stmt = $pdo->prepare("SELECT start_time, end_time, is_available FROM working_hours WHERE barber_id = ? AND day_of_week = ?");
        $stmt->execute([$barber_id, $dayOfWeek]);
        $working_hour = $stmt->fetch(PDO::FETCH_ASSOC);

        // If no specific schedule is found, assume closed. 
        if (!$working_hour || empty($working_hour['is_available'])) {
            echo json_encode(['data' => []]);
            exit;
        }

        // Generate 30-minute slots between start_time and end_time
        $start_time = strtotime($date . ' ' . $working_hour['start_time']);
        $end_time = strtotime($date . ' ' . $working_hour['end_time']);
        
        $slots = [];
        $current = $start_time;
        while ($current + 1800 <= $end_time) { // 30 mins
            $slots[] = date('H:i', $current);
            $current += 1800;
        }

        // Fetch booked appointments
        $stmt = $pdo->prepare("SELECT appointment_time FROM appointments WHERE barber_id = ? AND appointment_date = ? AND status IN ('pending', 'confirmed')");
        $stmt->execute([$barber_id, $date]);
        $booked_times = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Normalize booked times (e.g. '09:00:00' to '09:00')
        $booked_times = array_map(function($t) { return substr($t, 0, 5); }, $booked_times);

        // Filter booked slots
        $available_slots = array_values(array_filter($slots, function($slot) use ($booked_times) {
            return !in_array($slot, $booked_times);
        }));

        echo json_encode(['data' => $available_slots]);
        exit;
    }
}

// Contact form (POST)
if ($method === 'POST' && $path === '/public/contact') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $phone = $data['phone'] ?? '';
    $message = $data['message'] ?? '';

    // Get the destination email from settings
    $stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'contact_receiver_email'");
    $receiver = $stmt->fetchColumn();
    
    // Fallback to the public email or a default if receiver is not set
    if (!$receiver) {
        $stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'contact_email'");
        $receiver = $stmt->fetchColumn() ?: 'admin@candycutz.com';
    }

    // In a production environment, you would use PHPMailer or mail() to send the email here:
    // mail($receiver, "New Contact Form Submission from $name", $message, "From: $email");

    // We can also store the message in the database for the admin to view later
    $stmt = $pdo->prepare("INSERT INTO messages (sender_name, sender_email, sender_phone, message, recipient_email, created_at) 
                           VALUES (?, ?, ?, ?, ?, NOW())");
    try {
        $stmt->execute([$name, $email, $phone, $message, $receiver]);
    } catch (PDOException $e) {
        // Table might not exist yet, ignore for now as this is a mock
    }

    echo json_encode(['success' => true, 'message' => 'Message sent successfully. We will get back to you soon.']);
    exit;
}

// Admin Testimonials (GET)
if ($method === 'GET' && $path === '/admin/testimonials') {
    $stmt = $pdo->query("SELECT t.id, t.rating, t.comment, t.is_approved, t.created_at, u.name as client_name 
                        FROM testimonials t
                        JOIN users u ON t.customer_id = u.id
                        ORDER BY t.created_at DESC");
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['data' => $testimonials]);
    exit;
}

// Admin Testimonials (PUT - Update approval)
if ($method === 'PUT' && preg_match('/^\/admin\/testimonials\/(\d+)$/', $path, $matches)) {
    $id = $matches[1];
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['is_approved'])) {
        $is_approved = $data['is_approved'] ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE testimonials SET is_approved = ? WHERE id = ?");
        $stmt->execute([$is_approved, $id]);
        
        if ($is_approved) {
            $stmt = $pdo->prepare("SELECT customer_id FROM testimonials WHERE id = ?");
            $stmt->execute([$id]);
            $testimonial = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($testimonial) {
                // Determine admin user ID (or default to 1 for system)
                // In a real scenario, this would come from the auth token
                $adminId = 1; 
                $notifStmt = $pdo->prepare("INSERT INTO notifications (sender_id, recipient_type, recipient_id, type, title, message, related_entity_id) VALUES (?, 'customer', ?, 'review_approved', 'Review Approved', 'Your recent review has been approved and is now public.', ?)");
                $notifStmt->execute([$adminId, $testimonial['customer_id'], $id]);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Testimonial updated']);
        exit;
    }
}

// Admin Testimonials (DELETE)
if ($method === 'DELETE' && preg_match('/^\/admin\/testimonials\/(\d+)$/', $path, $matches)) {
    $id = $matches[1];
    $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Testimonial deleted']);
    exit;
}

// Authentication
if ($method === 'POST' && $path === '/auth/login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Using Laravel's Hash::make('password') logic: verify using password_verify
    if ($user && password_verify($password, $user['password'])) {
        // Log the login to audit_logs
        $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'login', 'user', ?, ?)")->execute([$user['id'], $user['id'], $_SERVER['REMOTE_ADDR'] ?? '']);
        
        // Generate JWT Token
        $payload = [
            'iss' => 'candycutz_api',
            'iat' => time(),
            'exp' => time() + (86400 * 30), // 30 days expiration
            'sub' => $user['id'],
            'role' => $user['role']
        ];
        $token = JWT::encode($payload, $jwtSecret, 'HS256');

        unset($user['password']);
        echo json_encode([
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ]);
    } else {
        error_log("Login failed for email: '$email'");
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
    exit;
}
// Get current authenticated user
if ($method === 'GET' && $path === '/auth/me') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthenticated']);
        exit;
    }

    try {
        $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
        $userId = $decoded->sub;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired token']);
        exit;
    }
    
    if ($userId > 0) {
        $stmt = $pdo->prepare("SELECT id, name, email, role, phone, avatar, notification_preferences, created_at, updated_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo json_encode(['data' => $user]);
            exit;
        }
    }
    
    http_response_code(401);
    echo json_encode(['error' => 'Unauthenticated']);
    exit;
}

// Logout
if ($method === 'POST' && $path === '/auth/logout') {
    echo json_encode(['success' => true, 'message' => 'Logged out']);
    exit;
}

// Global Notifications Endpoints
if (strpos($path, '/notifications') === 0) {
    // Authenticate
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!$token) {
        http_response_code(401); echo json_encode(['error' => 'Unauthenticated']); exit;
    }

    try {
        $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
        $userId = $decoded->sub;
    } catch (Exception $e) {
        http_response_code(401); echo json_encode(['error' => 'Invalid or expired token']); exit;
    }
    
    if (!$userId) {
        http_response_code(401); echo json_encode(['error' => 'Unauthenticated']); exit;
    }
    
    // Get user role
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $role = $stmt->fetchColumn();
    
    if ($method === 'GET' && $path === '/notifications') {
        // Fetch notifications for this user
        // Includes specific recipient_id, or role-based (e.g. 'all_customers', 'admin')
        $query = "SELECT * FROM notifications WHERE 
                  (recipient_id = ?) OR 
                  (recipient_type = ?) OR 
                  (recipient_type = 'admin' AND ? IN ('admin', 'super_admin')) OR
                  (recipient_type = 'all_customers' AND ? = 'customer')
                  ORDER BY created_at DESC LIMIT 100";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId, $role, $role, $role]);
        
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filter notifications based on customer preferences
        if ($role === 'customer') {
            $stmt = $pdo->prepare("SELECT notification_preferences FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $prefsJson = $stmt->fetchColumn();
            $prefs = $prefsJson ? json_decode($prefsJson, true) : [];

            $notifyBookings = $prefs['notify_bookings'] ?? true;
            $notifySystem = $prefs['notify_system'] ?? true;
            $notifyWishlist = $prefs['notify_wishlist'] ?? true;
            $notifyBlog = $prefs['notify_blog'] ?? true;
            $notifyGeneral = $prefs['notify_general'] ?? true;

            $filteredNotifications = [];
            foreach ($notifications as $n) {
                if ($n['type'] === 'booking' && !$notifyBookings) continue;
                if ($n['type'] === 'system_update' && !$notifySystem) continue;
                if ($n['type'] === 'wishlist_update' && !$notifyWishlist) continue;
                if ($n['type'] === 'blog_update' && !$notifyBlog) continue;
                if ($n['type'] === 'general_update' && !$notifyGeneral) continue;
                $filteredNotifications[] = $n;
            }
            $notifications = $filteredNotifications;
        }

        echo json_encode(['data' => $notifications]);
        exit;
    }
    
    if ($method === 'PATCH' && preg_match('/^\/notifications\/(\d+)\/read$/', $path, $matches)) {
        $notifId = $matches[1];
        // Allow updating if it matches user's scope (simplification: any auth user can mark read, ideally verify ownership)
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$notifId]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($method === 'PATCH' && $path === '/notifications/read-all') {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE recipient_id = ? OR recipient_type = ?");
        $stmt->execute([$userId, $role]);
        echo json_encode(['success' => true]);
        exit;
    }
    
    if ($method === 'DELETE' && preg_match('/^\/notifications\/(\d+)$/', $path, $matches)) {
        $notifId = $matches[1];
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->execute([$notifId]);
        echo json_encode(['success' => true]);
        exit;
    }
}

// Require Customer API Routes
if (strpos($path, '/customer/') === 0) {
    require __DIR__ . '/api_customer.php';
}

// Require Barber API Routes
if (strpos($path, '/barber/') === 0) {
    require __DIR__ . '/api_barber.php';
}

// Require Admin API Routes
if (strpos($path, '/admin/') === 0 && !strpos($path, '/admin/testimonials')) {
    require __DIR__ . '/api_admin.php';
}

// Default 404
http_response_code(404);
echo json_encode(['error' => 'Not found', 'path' => $path]);
?>
