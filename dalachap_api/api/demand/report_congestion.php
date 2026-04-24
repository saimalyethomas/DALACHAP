<?php
// =============================================
// API: Report Route Congestion
// Endpoint: POST /api/demand/report_congestion.php
// Body: { "route_id": 1, "stop_id": 3, "waiting_count": 25 }
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

// Authenticate (passenger or officer can report)
$user = requireRole($db, ['passenger', 'traffic_officer', 'driver', 'admin']);

// Get JSON input
$input = getJsonInput();

// Validate required fields
$errors = validateRequired($input, ['route_id', 'waiting_count']);
if (!empty($errors)) {
    sendValidationError($errors);
}

$route_id = intval($input['route_id']);
$stop_id = isset($input['stop_id']) ? intval($input['stop_id']) : null;
$waiting_count = intval($input['waiting_count']);
$latitude = isset($input['latitude']) ? floatval($input['latitude']) : null;
$longitude = isset($input['longitude']) ? floatval($input['longitude']) : null;

// Determine report type
$report_type = 'normal';
if ($waiting_count >= 30) {
    $report_type = 'overcrowded';
} elseif ($waiting_count >= 15) {
    $report_type = 'high_demand';
} elseif ($waiting_count <= 3) {
    $report_type = 'low_demand';
}

// Insert demand report
$insert_sql = "INSERT INTO demand_reports (route_id, stop_id, reported_by, passenger_waiting_count, 
                                           report_type, latitude, longitude, reported_at) 
               VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
$result = $database->executeQuery($insert_sql, [
    $route_id, $stop_id, $user['user_id'], $waiting_count, 
    $report_type, $latitude, $longitude
], "iiidssd");

if (!$result['success']) {
    sendErrorResponse("Failed to submit report", 500);
}

// Get route name for notification
$route = $database->fetchOne("SELECT route_name FROM routes WHERE route_id = ?", [$route_id], "i");

// Send notifications to traffic officers
$officers_sql = "SELECT user_id FROM users WHERE user_role = 'traffic_officer' AND is_active = 1";
$officers = $database->fetchAll($officers_sql);

foreach ($officers as $officer) {
    $notif_sql = "INSERT INTO notifications (user_id, title, message, notification_type, related_id) 
                  VALUES (?, '🚌 Route Congestion Alert', ?, 'demand_alert', ?)";
    $message = "High demand reported on {$route['route_name']}: {$waiting_count} passengers waiting.";
    $database->executeQuery($notif_sql, [$officer['user_id'], $message, $route_id], "isi");
}

sendSuccessResponse([
    "report_id" => $result['insert_id'],
    "report_type" => $report_type
], "Congestion report submitted successfully");
?>