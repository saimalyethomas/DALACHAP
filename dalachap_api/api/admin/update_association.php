<?php
require_once '../../config/cors.php';
require_once '../../config/db_connection.php';
require_once '../../includes/response.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse("Method not allowed", 405);
}

$user = requireRole($db, ['admin']);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || empty($data['association_id'])) {
        sendErrorResponse("Association ID is required", 422);
    }
    
    $stmt = $db->prepare("UPDATE associations SET association_name = ?, registration_number = ?, phone_number = ?, email = ?, chairman_name = ?, address = ?, status = ? WHERE association_id = ?");
    $stmt->bind_param(
        "sssssssi",
        $data['association_name'],
        $data['registration_number'],
        $data['phone_number'],
        $data['email'],
        $data['chairman_name'],
        $data['address'],
        $data['status'],
        $data['association_id']
    );
    
    if ($stmt->execute()) {
        sendSuccessResponse([], "Association updated successfully");
    } else {
        sendErrorResponse("Failed to update association: " . $db->error, 500);
    }
} catch (Exception $e) {
    sendErrorResponse("Failed to update association: " . $e->getMessage(), 500);
}
?>

