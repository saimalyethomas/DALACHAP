<?php
// =============================================
// API: User Registration
// Endpoint: POST /api/auth/register.php
// Body: { "full_name": "John Doe", "email": "john@example.com", 
//         "phone": "0712345678", "password": "password123", "role": "passenger" }
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/validation.php';

// Ensure database instance is available
if (!isset($database)) {
    sendErrorResponse("Database not initialized", 500);
}

// Only POST method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse("Method not allowed", 405);
}

// Get JSON input
$input = getJsonInput();

// Validate required fields
$errors = validateRequired($input, ['full_name', 'email', 'phone', 'password']);
if (!empty($errors)) {
    sendValidationError($errors);
}

// Sanitize inputs
$full_name = sanitizeInput($input['full_name']);
$email = sanitizeInput($input['email']);
$phone = sanitizeInput($input['phone']);
$password = $input['password'];
$role = isset($input['role']) ? sanitizeInput($input['role']) : 'passenger';

// Validate email format
if (!validateEmail($email)) {
    sendValidationError(['email' => 'Invalid email format']);
}

// Validate phone format
if (!validatePhone($phone)) {
    sendValidationError(['phone' => 'Invalid phone number. Use 10 digits (e.g., 0712345678)']);
}

// Validate password length
if (strlen($password) < 6) {
    sendValidationError(['password' => 'Password must be at least 6 characters']);
}

// Validate role
$allowed_roles = ['passenger', 'driver', 'association_leader', 'traffic_officer'];
if (!in_array($role, $allowed_roles)) {
    sendValidationError(['role' => 'Invalid role. Allowed: ' . implode(', ', $allowed_roles)]);
}

// Check if email already exists using Database helper
$email_check = $database->fetchOne("SELECT user_id FROM users WHERE email = ?", [$email]);
if ($email_check) {
    sendErrorResponse("Email already registered", 409);
}

// Check if phone already exists using Database helper
$phone_check = $database->fetchOne("SELECT user_id FROM users WHERE phone_number = ?", [$phone]);
if ($phone_check) {
    sendErrorResponse("Phone number already registered", 409);
}

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert user using Database helper
$insert_result = $database->executeQuery(
    "INSERT INTO users (full_name, email, phone_number, password_hash, user_role, is_active) VALUES (?, ?, ?, ?, ?, 1)",
    [$full_name, $email, $phone, $password_hash, $role]
);

if (isset($insert_result['error'])) {
    sendErrorResponse("Registration failed: " . $insert_result['error'], 500);
}

$user_id = $database->lastInsertId();

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Log registration (ignore log errors)
$database->executeQuery(
    "INSERT INTO system_logs (user_id, action, description, ip_address) VALUES (?, 'REGISTER', 'New user registered', ?)",
    [$user_id, $ip]
);

sendSuccessResponse([
    "user_id" => $user_id,
    "full_name" => $full_name,
    "email" => $email,
    "role" => $role
], "Registration successful", 201);
?>