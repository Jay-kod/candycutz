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
// SERVICES MANAGEMENT
// ==========================================
if (strpos($path, '/admin/services') === 0) {
    // GET all global services
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT s.*, c.name as category_name FROM services s LEFT JOIN service_categories c ON s.category_id = c.id ORDER BY s.name ASC");
        echo json_encode(['data' => $stmt->fetchAll()]);
        exit;
    }
}

// ==========================================
// GALLERY MANAGEMENT
// ==========================================
if (strpos($path, '/admin/gallery') === 0) {
    // GET all global gallery items
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT g.*, u.name as barber_name FROM gallery g LEFT JOIN barbers b ON g.barber_id = b.id LEFT JOIN users u ON b.user_id = u.id ORDER BY g.display_order ASC, g.created_at DESC");
        echo json_encode(['data' => $stmt->fetchAll()]);
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
