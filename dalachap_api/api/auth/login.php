<?php
// =============================================
// API: User Login
// Endpoint: POST /api/auth/login.php
// Body: { "email": "user@example.com", "password": "password123" }
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/validation.php';
require_once '../../includes/auth.php';

// Only POST method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse("Method not allowed", 405);
}

// Get JSON input
$input = getJsonInput();

// Validate required fields
$errors = validateRequired($input, ['email', 'password']);
if (!empty($errors)) {
    sendValidationError($errors);
}

$email = sanitizeInput($input['email']);
$password = $input['password']; // Don't sanitize password before verification

// Check if email exists
    $sql = "SELECT user_id, full_name, email, phone_number, password_hash, user_role, is_active 
            FROM users 
            WHERE email = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        sendErrorResponse("Invalid email or password", 401);
    }

    // Check if account is active
    if ($user['is_active'] != 1) {
        sendErrorResponse("Your account is deactivated. Please contact administrator.", 403);
    }

    // Verify password (using password_verify for hashed passwords)
    if (!password_verify($password, $user['password_hash'])) {
        sendErrorResponse("Invalid email or password", 401);
    }

// Generate token
$token = generateToken($user['user_id'], $user['email'], $user['user_role']);

// Log login activity
$log_sql = "INSERT INTO system_logs (user_id, action, description, ip_address) 
            VALUES (?, 'LOGIN', 'User logged in', ?)";
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$log_stmt = $db->prepare($log_sql);
$log_stmt->bind_param("is", $user['user_id'], $ip);
$log_stmt->execute();

// Prepare response data (exclude sensitive info)
$response_data = [
    "user" => [
        "user_id" => $user['user_id'],
        "full_name" => $user['full_name'],
        "email" => $user['email'],
        "phone_number" => $user['phone_number'],
        "role" => $user['user_role']
    ],
    "token" => $token,
    "token_expiry_hours" => TOKEN_EXPIRY_HOURS
];

sendSuccessResponse($response_data, "Login successful");
?>