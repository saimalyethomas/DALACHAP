<?php
// =============================================
// API: Revoke Route Authorization (Traffic Officer only)
// Endpoint: POST /api/authorizations/revoke_auth.php
// Body: { "authorization_id": 5, "revoke_reason": "Vehicle not complying" }
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

// Only traffic officers and admin can revoke
$user = requireRole($conn, ['traffic_officer', 'admin']);

// Get JSON input
$input = getJsonInput();

if (!isset($input['authorization_id']) || empty($input['authorization_id'])) {
    sendErrorResponse("authorization_id is required", 400);
}

$auth_id = intval($input['authorization_id']);
$revoke_reason = isset($input['revoke_reason']) ? sanitizeInput($input['revoke_reason']) : "Revoked by officer";

// Get authorization details
$auth = $database->fetchOne(
    "SELECT a.*, v.registration_number 
     FROM route_authorizations a
     JOIN daladala_vehicles v ON a.vehicle_id = v.vehicle_id
     WHERE a.authorization_id = ?",
    [$auth_id], "i"
);

if (!$auth) {
    sendErrorResponse("Authorization not found", 404);
}

if ($auth['status'] != 'active') {
    sendErrorResponse("Authorization is already " . $auth['status'], 400);
}

// Revoke the authorization
$update_sql = "UPDATE route_authorizations SET status = 'revoked' WHERE authorization_id = ?";
$result = $database->executeQuery($update_sql, [$auth_id], "i");

if ($result['success']) {
    // Log the action
    $database->executeQuery(
        "INSERT INTO system_logs (user_id, action, description, ip_address) VALUES (?, 'REVOKE_AUTH', ?, ?)",
        [$user['user_id'], "Revoked authorization #$auth_id for vehicle {$auth['registration_number']}. Reason: $revoke_reason", $_SERVER['REMOTE_ADDR'] ?? 'unknown'],
        "iss"
    );
    
    // Notify the association/driver
    $database->executeQuery(
        "INSERT INTO notifications (user_id, title, message, notification_type, related_id) 
         VALUES ((SELECT user_id FROM driver_assignments WHERE vehicle_id = ? AND is_current = 1 LIMIT 1), 
                 '⚠️ Authorization Revoked', ?, 'authorization', ?)",
        [$auth['vehicle_id'], "Your route authorization has been revoked. Reason: $revoke_reason", $auth_id],
        "isi"
    );
    
    sendSuccessResponse([], "Authorization revoked successfully");
} else {
    sendErrorResponse("Failed to revoke authorization", 500);
}
?>