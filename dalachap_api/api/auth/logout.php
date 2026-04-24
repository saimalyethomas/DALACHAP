<?php
// =============================================
// API: User Logout
// Endpoint: POST /api/auth/logout.php
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/auth.php';

// Only POST method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse("Method not allowed", 405);
}

// Authenticate user (optional - just log the logout)
$user = authenticate($db);

// Log logout activity
$log_sql = "INSERT INTO system_logs (user_id, action, description, ip_address) 
            VALUES (?, 'LOGOUT', 'User logged out', ?)";
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$log_stmt = $db->prepare($log_sql);
$log_stmt->bind_param("is", $user['user_id'], $ip);
$log_stmt->execute();

sendSuccessResponse([], "Logout successful");
?>