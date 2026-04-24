<?php
// =============================================
// API: Add New Route (Admin only)
// Endpoint: POST /api/admin/add_route.php
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

// Admin only
$user = requireRole($db, ['admin']);

$input = getJsonInput();

// Validate required fields
$errors = validateRequired($input, ['route_code', 'route_name', 'starting_point', 'ending_point', 'distance_km', 'base_fare']);
if (!empty($errors)) {
    sendValidationError($errors);
}

$route_code = sanitizeInput($input['route_code']);
$route_name = sanitizeInput($input['route_name']);
$starting_point = sanitizeInput($input['starting_point']);
$ending_point = sanitizeInput($input['ending_point']);
$distance_km = floatval($input['distance_km']);
$estimated_duration = isset($input['estimated_duration_minutes']) ? intval($input['estimated_duration_minutes']) : 0;
$base_fare = floatval($input['base_fare']);

// Check if route code exists
$check = $database->fetchOne("SELECT route_id FROM routes WHERE route_code = ?", [$route_code], "s");
if ($check) {
    sendErrorResponse("Route code already exists", 409);
}

// Insert route
$insert_sql = "INSERT INTO routes (route_code, route_name, starting_point, ending_point, distance_km, estimated_duration_minutes, base_fare, created_by) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$result = $database->executeQuery($insert_sql, 
    [$route_code, $route_name, $starting_point, $ending_point, $distance_km, $estimated_duration, $base_fare, $user['user_id']], 
    "ssssdddi"
);

if ($result['success']) {
    // Log activity
    $database->executeQuery(
        "INSERT INTO system_logs (user_id, action, description, ip_address) VALUES (?, 'ADD_ROUTE', ?, ?)",
        [$user['user_id'], "Added new route: $route_code - $route_name", $_SERVER['REMOTE_ADDR'] ?? 'unknown'],
        "iss"
    );
    
    sendSuccessResponse([
        "route_id" => $result['insert_id'],
        "route_code" => $route_code,
        "route_name" => $route_name
    ], "Route added successfully", 201);
} else {
    sendErrorResponse("Failed to add route", 500);
}
?>