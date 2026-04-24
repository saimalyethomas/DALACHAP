<?php
// =============================================
// API: Get Route Authorizations
// Endpoint: GET /api/authorizations/get_authorizations.php?status=active&vehicle_id=1
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/auth.php';

// Only GET method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse("Method not allowed", 405);
}

// Authenticate
$user = authenticate($conn);

// Build query
$sql = "SELECT a.authorization_id, a.vehicle_id, a.original_route_id, a.temporary_route_id,
               a.reason, a.start_datetime, a.end_datetime, a.status, a.created_at,
               v.registration_number,
               r1.route_name as original_route_name,
               r2.route_name as temporary_route_name,
               u.full_name as authorized_by_name
        FROM route_authorizations a
        JOIN daladala_vehicles v ON a.vehicle_id = v.vehicle_id
        LEFT JOIN routes r1 ON a.original_route_id = r1.route_id
        LEFT JOIN routes r2 ON a.temporary_route_id = r2.route_id
        JOIN users u ON a.authorized_by = u.user_id
        WHERE 1=1";
$params = [];
$types = "";

// Filter by status
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $sql .= " AND a.status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}

// Filter by vehicle
if (isset($_GET['vehicle_id']) && !empty($_GET['vehicle_id'])) {
    $sql .= " AND a.vehicle_id = ?";
    $params[] = intval($_GET['vehicle_id']);
    $types .= "i";
}

// Role-based restrictions
if ($user['user_role'] == 'driver') {
    $sql .= " AND a.vehicle_id IN (SELECT vehicle_id FROM driver_assignments WHERE driver_id = ? AND is_current = 1)";
    $params[] = $user['user_id'];
    $types .= "i";
} elseif ($user['user_role'] == 'association_leader') {
    $sql .= " AND a.vehicle_id IN (SELECT vehicle_id FROM daladala_vehicles WHERE association_id IN 
             (SELECT association_id FROM users WHERE user_id = ?))";
    $params[] = $user['user_id'];
    $types .= "i";
}

$sql .= " ORDER BY a.created_at DESC";

$authorizations = $database->fetchAll($sql, $params, $types);

sendSuccessResponse([
    "authorizations" => $authorizations,
    "total" => count($authorizations)
], "Authorizations retrieved successfully");
?>