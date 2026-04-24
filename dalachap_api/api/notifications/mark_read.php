<?php
// =============================================
// API: Mark Notification as Read
// Endpoint: POST /api/notifications/mark_read.php
// Body: { "notification_id": 10 } or { "mark_all": true }
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

// Authenticate user
$user = authenticate($conn);

$input = getJsonInput();

// Mark all notifications as read
if (isset($input['mark_all']) && $input['mark_all'] === true) {
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
    $result = $database->executeQuery($update_sql, [$user['user_id']], "i");
    
    sendSuccessResponse([], "All notifications marked as read");
}

// Mark single notification
if (!isset($input['notification_id']) || empty($input['notification_id'])) {
    sendErrorResponse("notification_id is required", 400);
}

$notification_id = intval($input['notification_id']);

// Verify notification belongs to user
$check = $database->fetchOne(
    "SELECT notification_id FROM notifications WHERE notification_id = ? AND user_id = ?",
    [$notification_id, $user['user_id']], "ii"
);

if (!$check) {
    sendErrorResponse("Notification not found", 404);
}

$update_sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
$result = $database->executeQuery($update_sql, [$notification_id], "i");

if ($result['success']) {
    sendSuccessResponse([], "Notification marked as read");
} else {
    sendErrorResponse("Failed to mark notification as read", 500);
}
?>