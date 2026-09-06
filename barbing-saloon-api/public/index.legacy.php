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

// Catch non-API routes and serve the Vue frontend
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($requestUri, '/api') !== 0) {
    if (file_exists(__DIR__ . $requestUri) && is_file(__DIR__ . $requestUri)) {
        return false; // serve the requested resource as-is
    } else {
        readfile(__DIR__ . '/index.html');
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

require_once __DIR__ . '/notification_helper.php';
require_once __DIR__ . '/mail_helper.php';
require_once __DIR__ . '/social_auth_helper.php';
cc_ensure_notifications_schema($pdo);
cc_ensure_mail_schema($pdo);

// ═══════════════════════════════════════════════════
// PERFORMANCE: Gzip compression for all responses
// ═══════════════════════════════════════════════════
if (!headers_sent() && extension_loaded('zlib') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
    ob_start('ob_gzhandler');
}

// ═══════════════════════════════════════════════════
// RATE LIMITING - Protect server from abuse
// ═══════════════════════════════════════════════════

// Auto-create rate_limits table ONCE (check via marker file)
$markerFile = sys_get_temp_dir() . '/candycutz_rate_limits_created.marker';
if (!file_exists($markerFile)) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS rate_limits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        endpoint VARCHAR(100) NOT NULL DEFAULT 'global',
        request_count INT NOT NULL DEFAULT 1,
        window_start DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip_endpoint (ip_address, endpoint),
        INDEX idx_window (window_start)
    ) ENGINE=InnoDB");
    @file_put_contents($markerFile, time());
}

// Clean up expired records only ~5% of requests (not every request)
if (mt_rand(1, 20) === 1) {
    $pdo->exec("DELETE FROM rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
}

// Rate limit configuration: [max_requests, window_seconds]
$rateLimits = [
    '/auth/login'    => [5, 60],     // 5 login attempts per minute (anti brute-force)
    '/auth/register' => [3, 60],     // 3 registrations per minute
    '/auth/social-login' => [10, 60],
    '/auth/forgot-password' => [3, 60],
    '/auth/reset-password' => [3, 60],
    '/public/contact'=> [3, 60],     // 3 contact messages per minute
    'default'        => [60, 60],    // 60 requests per minute for everything else
];

$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if (strpos($clientIp, ',') !== false) {
    $clientIp = trim(explode(',', $clientIp)[0]);
}

// Parse request path early for rate limiting
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('/^\/api/', '', $path);
$method = $_SERVER['REQUEST_METHOD'];

// Determine which rate limit rule applies
$rateLimitKey = 'default';
foreach ($rateLimits as $pattern => $limits) {
    if ($pattern !== 'default' && strpos($path, $pattern) === 0) {
        $rateLimitKey = $pattern;
        break;
    }
}

list($maxRequests, $windowSeconds) = $rateLimits[$rateLimitKey];

// Check current request count
$stmt = $pdo->prepare("SELECT id, request_count, window_start FROM rate_limits WHERE ip_address = ? AND endpoint = ? AND window_start > DATE_SUB(NOW(), INTERVAL ? SECOND) ORDER BY window_start DESC LIMIT 1");
$stmt->execute([$clientIp, $rateLimitKey, $windowSeconds]);
$rateRecord = $stmt->fetch(PDO::FETCH_ASSOC);

if ($rateRecord) {
    if ($rateRecord['request_count'] >= $maxRequests) {
        $windowStart = strtotime($rateRecord['window_start']);
        $retryAfter = max(1, ($windowStart + $windowSeconds) - time());
        http_response_code(429);
        header("Retry-After: $retryAfter");
        header("X-RateLimit-Limit: $maxRequests");
        header("X-RateLimit-Remaining: 0");
        echo json_encode(['error' => 'Too many requests. Please try again later.', 'retry_after' => $retryAfter]);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE rate_limits SET request_count = request_count + 1 WHERE id = ?");
    $stmt->execute([$rateRecord['id']]);
    $remaining = $maxRequests - $rateRecord['request_count'] - 1;
} else {
    $stmt = $pdo->prepare("INSERT INTO rate_limits (ip_address, endpoint, request_count, window_start) VALUES (?, ?, 1, NOW())");
    $stmt->execute([$clientIp, $rateLimitKey]);
    $remaining = $maxRequests - 1;
}

header("X-RateLimit-Limit: $maxRequests");
header("X-RateLimit-Remaining: " . max(0, $remaining));

// ═══════════════════════════════════════════════════
// PERFORMANCE: Server-side cache for public endpoints
// ═══════════════════════════════════════════════════
$cacheDir = sys_get_temp_dir() . '/candycutz_cache';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

/**
 * Try to serve a cached response. Returns true if cache was served.
 * Cache TTL is in seconds. Public GET endpoints are cached to avoid
 * hitting the database on every single page load.
 */
function serveFromCache($cacheDir, $cacheKey, $ttl = 60) {
    $cacheFile = $cacheDir . '/' . md5($cacheKey) . '.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        header('X-Cache: HIT');
        header('Cache-Control: public, max-age=240, stale-while-revalidate=300');
        echo file_get_contents($cacheFile);
        return true;
    }
    return false;
}

function writeToCache($cacheDir, $cacheKey, $data) {
    $cacheFile = $cacheDir . '/' . md5($cacheKey) . '.json';
    @file_put_contents($cacheFile, $data);
}

/**
 * Flush all cached public endpoints so admin/customer mutations
 * become visible immediately instead of waiting for the TTL.
 */
function cc_flush_public_cache($cacheDir) {
    foreach (glob($cacheDir . '/*.json') as $f) {
        @unlink($f);
    }
}

// ═══════════════════════════════════════════════════
// API Routes
// ═══════════════════════════════════════════════════
if ($method === 'GET') {
    // Public settings (cached for 5 minutes)
    if ($path === '/public/settings') {
        if (serveFromCache($cacheDir, 'public_settings', 300)) exit;
        
        $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
        $settings = [];
        $hiddenSettings = ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_from', 'mail_from_name'];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (in_array($row['key'], $hiddenSettings, true)) continue;
            $settings[$row['key']] = $row['value'];
        }
        $response = json_encode(['data' => ($settings ?: ['name' => 'CandyCutz'])]);
        writeToCache($cacheDir, 'public_settings', $response);
        header('X-Cache: MISS');
        header('Cache-Control: public, max-age=240, stale-while-revalidate=300');
        echo $response;
        exit;
    }

    // Public services list (cached for 5 minutes)
    if ($path === '/public/services') {
        if (serveFromCache($cacheDir, 'public_services', 300)) exit;
        
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

        $barberStmt = $pdo->query("SELECT b.id, u.name, u.avatar, b.rating, b.specialties FROM barbers b
                                   JOIN users u ON b.user_id = u.id
                                   WHERE b.is_available = 1 AND b.deleted_at IS NULL");
        $allBarbers = $barberStmt->fetchAll(PDO::FETCH_ASSOC);

        $skillCategoryMap = [
            'fades' => 1, 'modern_cuts' => 1,
            'beard_grooming' => 2, 'beard_design' => 2, 'hot_shave' => 2
        ];

        $barberMatchesService = function ($serviceName, $categoryId, $barber) use ($skillCategoryMap) {
            $specialties = json_decode($barber['specialties'] ?? '[]', true);
            if (!is_array($specialties)) return false;
            $needle = strtolower(trim($serviceName));
            foreach ($specialties as $sp) {
                if (strtolower(trim((string)$sp)) === $needle) return true;
            }
            foreach ($specialties as $sp) {
                $tag = strtolower(trim((string)$sp));
                if (isset($skillCategoryMap[$tag]) && (int)$skillCategoryMap[$tag] === (int)$categoryId) return true;
            }
            return false;
        };

        foreach ($services as &$service) {
            if ($service['category_name']) {
                $service['category'] = [
                    'id' => $service['category_id'],
                    'name' => $service['category_name']
                ];
            }
            $service['barber'] = $service['barber_id']
                ? [
                    'id' => $service['barber_id'],
                    'name' => $service['barber_name'],
                    'avatar' => $service['barber_avatar']
                ]
                : null;
            $matching = [];
            foreach ($allBarbers as $ab) {
                if ($barberMatchesService($service['name'], $service['category_id'], $ab)) {
                    $matching[] = [
                        'id' => $ab['id'],
                        'name' => $ab['name'],
                        'avatar' => $ab['avatar'],
                        'rating' => $ab['rating']
                    ];
                }
            }
            $service['barbers'] = $matching;
            unset($service['category_id'], $service['category_name'], $service['barber_name'], $service['barber_avatar']);
        }
        
        $response = json_encode(['data' => $services, 'count' => count($services)]);
        writeToCache($cacheDir, 'public_services', $response);
        header('X-Cache: MISS');
        header('Cache-Control: public, max-age=240, stale-while-revalidate=300');
        echo $response;
        exit;
    }

    // Public barbers list (cached for 5 minutes)
    if ($path === '/public/barbers') {
        if (serveFromCache($cacheDir, 'public_barbers', 300)) exit;
        
        $stmt = $pdo->query("SELECT b.id, u.name, u.avatar, b.bio, b.rating, b.experience_years as years_experience, b.specialties, b.status, (b.rating >= 4.8) as is_featured FROM barbers b 
                            JOIN users u ON b.user_id = u.id WHERE b.is_available = 1");
        $barbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($barbers as &$barber) {
            if (isset($barber['specialties'])) {
                $barber['specialties'] = json_decode($barber['specialties'], true);
            }
        }
        
        $response = json_encode(['data' => $barbers, 'count' => count($barbers)]);
        writeToCache($cacheDir, 'public_barbers', $response);
        header('X-Cache: MISS');
        header('Cache-Control: public, max-age=240, stale-while-revalidate=300');
        echo $response;
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
        if (serveFromCache($cacheDir, 'public_gallery', 300)) exit;
        $stmt = $pdo->query("SELECT g.*, u.name as barber_name FROM gallery g LEFT JOIN barbers b ON g.barber_id = b.id LEFT JOIN users u ON b.user_id = u.id ORDER BY g.is_featured DESC, g.created_at DESC LIMIT 20");
        $gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response = json_encode(['data' => $gallery, 'count' => count($gallery)]);
        writeToCache($cacheDir, 'public_gallery', $response);
        header('Cache-Control: public, max-age=240, stale-while-revalidate=300');
        echo $response;
        exit;
    }

    // Public testimonials
    if ($path === '/public/testimonials') {
        $serviceId = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;
        $cacheKey = 'public_testimonials' . ($serviceId ? '_' . $serviceId : '');
        if (serveFromCache($cacheDir, $cacheKey, 300)) exit;
        
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
        $response = json_encode(['data' => $testimonials, 'count' => count($testimonials)]);
        writeToCache($cacheDir, $cacheKey, $response);
        header('Cache-Control: public, max-age=240, stale-while-revalidate=300');
        echo $response;
        exit;
    }

    // Service categories
    if ($path === '/public/service-categories') {
        if (serveFromCache($cacheDir, 'public_categories', 300)) exit;
        $stmt = $pdo->query("SELECT id, name, description, icon FROM service_categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response = json_encode(['data' => $categories]);
        writeToCache($cacheDir, 'public_categories', $response);
        header('Cache-Control: public, max-age=240, stale-while-revalidate=300');
        echo $response;
        exit;
    }

    // Blog posts
    if ($path === '/public/blog') {
        if (serveFromCache($cacheDir, 'public_blog', 300)) exit;
        $stmt = $pdo->query("SELECT p.id, p.title, p.slug, p.excerpt, p.featured_image, p.created_at, COALESCE(NULLIF(p.author_display, ''), u.name) as author_name,
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
        
        $response = json_encode(['data' => $formattedPosts]);
        writeToCache($cacheDir, 'public_blog', $response);
        header('Cache-Control: public, max-age=240, stale-while-revalidate=300');
        echo $response;
        exit;
    }

    // Single blog post
    if (preg_match('/^\/public\/blog\/([a-zA-Z0-9-]+)$/', $path, $matches)) {
        $slug = $matches[1];
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);
        $parts = explode('_', $token);
        $userId = isset($parts[2]) ? intval($parts[2]) : 0;
        
        $stmt = $pdo->prepare("SELECT p.id, p.title, p.slug, p.content, p.featured_image, p.created_at, COALESCE(NULLIF(p.author_display, ''), u.name) as author_name,
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

    cc_notify_admin($pdo, [
        'sender_id' => null,
        'type' => 'general_update',
        'title' => 'New Contact Message',
        'message' => sprintf('Contact form submission from %s (%s).', $name ?: 'Guest', $email ?: $phone ?: 'no contact info'),
    ]);

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
                cc_notify_customer($pdo, (int) $testimonial['customer_id'], [
                    'sender_id' => null,
                    'type' => 'review_approved',
                    'title' => 'Review Approved',
                    'message' => 'Your recent review has been approved and is now public.',
                    'related_entity_id' => (int) $id,
                ]);
                cc_notify_admin($pdo, [
                    'sender_id' => null,
                    'type' => 'review_approved',
                    'title' => 'Review Approved',
                    'message' => 'A customer review was approved and is now public.',
                    'related_entity_id' => (int) $id,
                ]);
            }
        } else {
            cc_notify_admin($pdo, [
                'sender_id' => null,
                'type' => 'review',
                'title' => 'Review Unapproved',
                'message' => 'A customer review was marked as not approved.',
                'related_entity_id' => (int) $id,
            ]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Testimonial updated']);
        cc_flush_public_cache($cacheDir);
        exit;
    }
}

// Admin Testimonials (DELETE)
if ($method === 'DELETE' && preg_match('/^\/admin\/testimonials\/(\d+)$/', $path, $matches)) {
    $id = $matches[1];
    $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    cc_notify_admin($pdo, [
        'sender_id' => null,
        'type' => 'review',
        'title' => 'Review Deleted',
        'message' => 'A customer review was removed from the system.',
        'related_entity_id' => (int) $id,
    ]);
    echo json_encode(['success' => true, 'message' => 'Testimonial deleted']);
    cc_flush_public_cache($cacheDir);
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
        
        cc_send_login_alert_email($pdo, $user['email'], $user['name']);
        
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

// Register a new customer account
if ($method === 'POST' && $path === '/auth/register') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = trim($data['name'] ?? '');
    $email = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';
    $passwordConfirmation = $data['password_confirmation'] ?? '';
    $phone = trim($data['phone'] ?? '');

    if (strlen($name) < 2) {
        http_response_code(422);
        echo json_encode(['error' => 'Name must be at least 2 characters']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['error' => 'Enter a valid email']);
        exit;
    }
    if (strlen($password) < 8) {
        http_response_code(422);
        echo json_encode(['error' => 'Password must be at least 8 characters']);
        exit;
    }
    if ($password !== $passwordConfirmation) {
        http_response_code(422);
        echo json_encode(['error' => 'Passwords do not match']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn()) {
        http_response_code(409);
        echo json_encode(['error' => 'An account with this email already exists']);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role, created_at, updated_at) VALUES (?, ?, ?, ?, 'customer', NOW(), NOW())");
    $stmt->execute([$name, $email, $hashed, $phone]);
    $userId = (int) $pdo->lastInsertId();

    $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'register', 'user', ?, ?)")->execute([$userId, $userId, $_SERVER['REMOTE_ADDR'] ?? '']);

    cc_send_welcome_email($pdo, $email, $name);

    $payload = [
        'iss' => 'candycutz_api',
        'iat' => time(),
        'exp' => time() + (86400 * 30),
        'sub' => $userId,
        'role' => 'customer'
    ];
    $token = JWT::encode($payload, $jwtSecret, 'HS256');

    echo json_encode([
        'data' => [
            'token' => $token,
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'customer',
                'phone' => $phone,
                'avatar' => null,
            ]
        ]
    ]);
    exit;
}

// Social login (Google / Apple id_token)
if ($method === 'POST' && $path === '/auth/social-login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $provider = $data['provider'] ?? '';
    $idToken = $data['id_token'] ?? '';
    $userData = $data['user_data'] ?? [];

    if (!in_array($provider, ['google', 'apple'], true) || $idToken === '') {
        http_response_code(422);
        echo json_encode(['error' => 'Provider and id_token are required']);
        exit;
    }

    if ($provider === 'google') {
        $clientId = cc_setting($pdo, 'google_client_id', '');
        $payload = cc_verify_google_id_token($idToken);
    } else {
        $clientId = cc_setting($pdo, 'apple_client_id', '');
        $payload = cc_verify_apple_id_token($idToken);
    }

    if ($payload === null) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired social token']);
        exit;
    }

    $expectedAud = $clientId;
    if ($expectedAud !== '' && ($payload['aud'] ?? '') !== '' && $payload['aud'] !== $expectedAud) {
        http_response_code(401);
        echo json_encode(['error' => 'Social token audience mismatch']);
        exit;
    }

    $socialEmail = strtolower(trim($payload['email'] ?? ''));
    if ($socialEmail === '') {
        http_response_code(422);
        echo json_encode(['error' => 'Social account has no email address']);
        exit;
    }
    $providerId = (string) ($payload['sub'] ?? '');
    $isVerified = !empty($payload['email_verified']);

    $fullName = trim($userData['name'] ?? '');
    if ($fullName === '') {
        $fullName = trim(($payload['name'] ?? '') . ' ' . ($payload['family_name'] ?? ''));
    }
    if ($fullName === '') {
        $fullName = $socialEmail;
    }
    $avatar = trim($payload['picture'] ?? '');
    $socialPhone = trim($userData['phone'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$socialEmail]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $userId = (int) $existing['id'];
        if ($existing['auth_provider'] === null || $existing['auth_provider'] === '') {
            $pdo->prepare("UPDATE users SET auth_provider = ?, provider_id = ?, updated_at = NOW() WHERE id = ?")->execute([$provider, $providerId, $userId]);
        }
    } else {
        $hashed = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role, avatar, auth_provider, provider_id, created_at, updated_at) VALUES (?, ?, ?, ?, 'customer', ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$fullName, $socialEmail, $hashed, $socialPhone, $avatar, $provider, $providerId]);
        $userId = (int) $pdo->lastInsertId();
        cc_send_welcome_email($pdo, $socialEmail, $fullName);
    }

    $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?, 'social_login', 'user', ?, ?)")->execute([$userId, $userId, $_SERVER['REMOTE_ADDR'] ?? '']);
    if ($existing) {
        cc_send_login_alert_email($pdo, $socialEmail, $existing['name']);
    }

    $payload = [
        'iss' => 'candycutz_api',
        'iat' => time(),
        'exp' => time() + (86400 * 30),
        'sub' => $userId,
        'role' => 'customer'
    ];
    $token = JWT::encode($payload, $jwtSecret, 'HS256');

    $stmt = $pdo->prepare("SELECT id, name, email, role, phone, avatar, notification_preferences, created_at, updated_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['data' => ['token' => $token, 'user' => $userRow]]);
    exit;
}

// Request a password reset link
if ($method === 'POST' && $path === '/auth/forgot-password') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = strtolower(trim($data['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['error' => 'Enter a valid email']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE email = ? AND used_at IS NULL")->execute([$email]);

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $tokenHash, date('Y-m-d H:i:s', time() + 1800)]);

        $appUrl = rtrim((string) cc_setting($pdo, 'app_url', ''), '/');
        if ($appUrl === '') {
            $appUrl = ($_SERVER['HTTPS'] ?? '') ? 'https://' : 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        }
        $resetLink = $appUrl . '/reset-password?token=' . $token . '&email=' . urlencode($email);
        cc_send_password_reset_email($pdo, $email, $user['name'], $resetLink);
    }

    echo json_encode(['success' => true, 'message' => 'If an account exists for this email, a reset link has been sent.']);
    exit;
}

// Reset password using a reset token
if ($method === 'POST' && $path === '/auth/reset-password') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = strtolower(trim($data['email'] ?? ''));
    $token = trim($data['token'] ?? '');
    $password = $data['password'] ?? '';
    $passwordConfirmation = $data['password_confirmation'] ?? '';

    if (strlen($password) < 8) {
        http_response_code(422);
        echo json_encode(['error' => 'Password must be at least 8 characters']);
        exit;
    }
    if ($password !== $passwordConfirmation) {
        http_response_code(422);
        echo json_encode(['error' => 'Passwords do not match']);
        exit;
    }

    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND token_hash = ? AND used_at IS NULL AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$email, $tokenHash]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid or expired reset token']);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?")->execute([$hashed, $email]);
    $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?")->execute([$reset['id']]);
    $pdo->prepare("DELETE FROM password_resets WHERE email = ? AND used_at IS NOT NULL AND created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)")->execute([$email]);

    $stmt = $pdo->prepare("SELECT name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $userName = $stmt->fetchColumn() ?: $email;
    cc_send_password_changed_email($pdo, $email, $userName);

    echo json_encode(['success' => true, 'message' => 'Your password has been reset. You can now sign in.']);
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
    $token = str_replace('Bearer ', '', $authHeader);
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
        $notifications = cc_fetch_notifications_for_user($pdo, (int) $userId, $role);

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
        cc_mark_all_notifications_read($pdo, (int) $userId, $role);
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
