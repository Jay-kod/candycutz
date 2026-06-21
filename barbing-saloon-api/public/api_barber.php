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
    
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $image_url = $data['image_url'] ?? '';
        $title = $data['title'] ?? 'Style';
        $description = $data['description'] ?? '';
        $category = $data['category'] ?? 'Haircut';
        
        if (!$image_url) {
            http_response_code(400); echo json_encode(['error' => 'Image URL required']); exit;
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
}

// ==========================================
// BLOG MANAGEMENT
// ==========================================
if (strpos($path, '/barber/blog') === 0) {
    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE author_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]); // blog_posts uses author_id which is users.id
        echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }
    
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';
        $excerpt = $data['excerpt'] ?? '';
        $is_published = isset($data['is_published']) ? ($data['is_published'] ? 1 : 0) : 0;
        
        if (!$title || !$content) {
            http_response_code(400); echo json_encode(['error' => 'Title and content required']); exit;
        }
        
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . time();
        
        $stmt = $pdo->prepare("INSERT INTO blog_posts (author_id, title, slug, content, excerpt, is_published) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $slug, $content, $excerpt, $is_published]);
        echo json_encode(['success' => true, 'message' => 'Blog post created']);
        exit;
    }

    if ($method === 'PUT' && preg_match('/^\/barber\/blog\/(\d+)$/', $path, $matches)) {
        $id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';
        $excerpt = $data['excerpt'] ?? '';
        $is_published = isset($data['is_published']) ? ($data['is_published'] ? 1 : 0) : 0;
        
        $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, content = ?, excerpt = ?, is_published = ? WHERE id = ? AND author_id = ?");
        $stmt->execute([$title, $content, $excerpt, $is_published, $id, $userId]);
        echo json_encode(['success' => true, 'message' => 'Blog post updated']);
        exit;
    }

    if ($method === 'DELETE' && preg_match('/^\/barber\/blog\/(\d+)$/', $path, $matches)) {
        $id = $matches[1];
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ? AND author_id = ?");
        $stmt->execute([$id, $userId]);
        echo json_encode(['success' => true, 'message' => 'Blog post deleted']);
        exit;
    }
}
