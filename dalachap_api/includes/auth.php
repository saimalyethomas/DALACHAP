<?php
// =============================================
// File: includes/auth.php
// Authentication Functions (JWT-like simplified)
// =============================================

require_once __DIR__ . '/../config/config.php';

// Generate a simple token (for development)
// In production, use proper JWT library
function generateToken($user_id, $email, $role) {
    $payload = [
        'user_id' => $user_id,
        'email' => $email,
        'role' => $role,
        'expiry' => time() + (TOKEN_EXPIRY_HOURS * 3600)
    ];
    
    // Simple base64 encoding (for development only)
    // In production, use proper JWT with signature
    $token = base64_encode(json_encode($payload));
    return $token;
}

// Verify and decode token
function verifyToken($token) {
    try {
        $payload = json_decode(base64_decode($token), true);
        
        if (!$payload) {
            return null;
        }
        
        // Check expiry
        if (isset($payload['expiry']) && $payload['expiry'] < time()) {
            return null;
        }
        
        return $payload;
    } catch (Exception $e) {
        return null;
    }
}

// Get token from Authorization header
function getBearerToken() {
    $headers = getallheaders();
    
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        $parts = explode(' ', $authHeader);
        
        if (count($parts) == 2 && $parts[0] == 'Bearer') {
            return $parts[1];
        }
    }
    
    // Also check query parameter (for GET requests)
    if (isset($_GET['token'])) {
        return $_GET['token'];
    }
    
    return null;
}

// Authenticate user and return user data
function authenticate($db_connection) {
    $token = getBearerToken();
    
    if (!$token) {
        sendErrorResponse("Authentication token required", 401);
    }
    
    $payload = verifyToken($token);
    
    if (!$payload) {
        sendErrorResponse("Invalid or expired token", 401);
    }
    
    // Verify user exists in database
    $sql = "SELECT user_id, full_name, email, user_role, phone_number, is_active FROM users WHERE user_id = ? AND is_active = 1";
    $stmt = $db_connection->prepare($sql);
    $stmt->bind_param("i", $payload['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        sendErrorResponse("User not found or inactive", 401);
    }
    
    return $user;
}

// Check if user has required role
function requireRole($db_connection, $allowed_roles) {
    $user = authenticate($db_connection);
    
    if (!in_array($user['user_role'], $allowed_roles)) {
        sendErrorResponse("Access denied. Required role: " . implode(', ', $allowed_roles), 403);
    }
    
    return $user;
}
?>