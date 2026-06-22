<?php

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
$host = 'localhost';
$db = 'candycutz_db';
$user = 'root';
$pass = '';

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
            SELECT s.id, s.name, s.description, s.image, s.price, s.duration_minutes, 
                   c.id as category_id, c.name as category_name 
            FROM services s
            LEFT JOIN service_categories c ON s.category_id = c.id
            WHERE s.is_available = 1
        ");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format category object for frontend
        foreach ($services as &$service) {
            if ($service['category_name']) {
                $service['category'] = [
                    'id' => $service['category_id'],
                    'name' => $service['category_name']
                ];
            }
            unset($service['category_id'], $service['category_name']);
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
        $stmt = $pdo->query("SELECT id, barber_id, title, category, image_path, description, is_featured FROM gallery ORDER BY is_featured DESC, created_at DESC LIMIT 20");
        $gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['data' => $gallery, 'count' => count($gallery)]);
        exit;
    }

    // Public testimonials
    if ($path === '/public/testimonials') {
        $stmt = $pdo->query("SELECT t.id, t.rating, t.comment, u.name as customer_name, b.id as barber_id 
                            FROM testimonials t
                            JOIN users u ON t.customer_id = u.id
                            LEFT JOIN barbers b ON t.barber_id = b.id
                            WHERE t.is_approved = 1 
                            ORDER BY t.created_at DESC LIMIT 10");
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
        // Simplified: return general availability
        echo json_encode(['data' => [
            '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '14:00', '14:30', '15:00', '15:30', '16:00'
        ]]);
        exit;
    }
}

// Contact form (POST)
if ($method === 'POST' && $path === '/public/contact') {
    $data = json_decode(file_get_contents('php://input'), true);
    // Store contact message (simplified)
    echo json_encode(['success' => true, 'message' => 'Contact form received']);
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
        unset($user['password']);
        echo json_encode([
            'data' => [
                'token' => 'dummy_token_' . $user['id'] . '_' . time(),
                'user' => $user
            ]
        ]);
    } else {
        error_log("Login failed for email: '$email' (password matched: " . ($user && password_verify($password, $user['password']) ? 'yes' : 'no') . ", user exists: " . ($user ? 'yes' : 'no') . ")");
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
    exit;
}
// Get current authenticated user
if ($method === 'GET' && $path === '/auth/me') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    
    if (!$token || strpos($token, 'dummy_token_') !== 0) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthenticated']);
        exit;
    }
    
    // Extract user_id from token format: dummy_token_{user_id}_{timestamp}
    $parts = explode('_', $token);
    $userId = isset($parts[2]) ? intval($parts[2]) : 0;
    
    if ($userId > 0) {
        $stmt = $pdo->prepare("SELECT id, name, email, role, phone, avatar, created_at, updated_at FROM users WHERE id = ?");
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
