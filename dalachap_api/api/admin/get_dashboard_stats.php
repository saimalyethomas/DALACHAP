<?php
// =============================================
// API: Admin Dashboard Statistics
// Endpoint: GET /api/admin/get_dashboard_stats.php
// Returns: Total users, routes, vehicles, associations
// =============================================

require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/auth.php';

// Only GET method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse("Method not allowed", 405);
}

// Authenticate and require admin role
$user = requireRole($db, ['admin']);

try {
    // Total users count
    $usersResult = $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $totalUsers = $usersResult->fetch_assoc()['count'] ?? 0;

    // Total active routes count
    $routesResult = $db->query("SELECT COUNT(*) as count FROM routes WHERE status = 'active'");
    $totalRoutes = $routesResult->fetch_assoc()['count'] ?? 0;

    // Total active vehicles count
    $vehiclesResult = $db->query("SELECT COUNT(*) as count FROM daladala_vehicles WHERE status = 'active'");
    $totalVehicles = $vehiclesResult->fetch_assoc()['count'] ?? 0;

    // Total associations count
    $assocResult = $db->query("SELECT COUNT(*) as count FROM associations WHERE status = 'active'");
    $totalAssociations = $assocResult->fetch_assoc()['count'] ?? 0;

    // Recent registrations (last 7 days) for the chart
    $chartResult = $db->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date ASC");
    $recentRegistrations = [];
    while ($row = $chartResult->fetch_assoc()) {
        $recentRegistrations[] = [
            'date' => $row['date'],
            'count' => (int)$row['count']
        ];
    }

    $response_data = [
        "total_users" => (int)$totalUsers,
        "total_routes" => (int)$totalRoutes,
        "total_vehicles" => (int)$totalVehicles,
        "total_associations" => (int)$totalAssociations,
        "recent_registrations" => $recentRegistrations
    ];

    sendSuccessResponse($response_data, "Dashboard statistics retrieved successfully");

} catch (Exception $e) {
    sendErrorResponse("Failed to retrieve dashboard statistics: " . $e->getMessage(), 500);
}
?>

