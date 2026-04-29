<?php
require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse("Method not allowed", 405);
}

$user = requireRole($db, ['admin', 'association_leader']);

try {
    $associationId = isset($_GET['association_id']) ? intval($_GET['association_id']) : null;
    
    if ($associationId) {
        $stmt = $db->prepare("SELECT * FROM associations WHERE association_id = ?");
        $stmt->bind_param("i", $associationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $association = $result->fetch_assoc();
        
        if ($association) {
            sendSuccessResponse(["association" => $association], "Association retrieved successfully");
        } else {
            sendErrorResponse("Association not found", 404);
        }
    } else {
        $sql = "SELECT a.*, COUNT(v.vehicle_id) as vehicle_count 
                FROM associations a 
                LEFT JOIN daladala_vehicles v ON a.association_id = v.association_id 
                GROUP BY a.association_id 
                ORDER BY a.association_name ASC";
        $result = $db->query($sql);
        
        $associations = [];
        while ($row = $result->fetch_assoc()) {
            $associations[] = $row;
        }
        
        sendSuccessResponse(["associations" => $associations], "Associations retrieved successfully");
    }
} catch (Exception $e) {
    sendErrorResponse("Failed to retrieve associations: " . $e->getMessage(), 500);
}
?>

