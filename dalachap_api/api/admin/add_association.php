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
    
    if (!$data) {
        sendErrorResponse("Invalid JSON data", 400);
    }
    
    $required = ['association_name', 'registration_number', 'phone_number'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendErrorResponse("Missing required field: $field", 422);
        }
    }
    
    $stmt = $db->prepare("INSERT INTO associations (association_name, registration_number, phone_number, email, chairman_name, address, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $status = $data['status'] ?? 'active';
    $stmt->bind_param(
        "sssssss",
        $data['association_name'],
        $data['registration_number'],
        $data['phone_number'],
        $data['email'],
        $data['chairman_name'],
        $data['address'],
        $status
    );
    
    if ($stmt->execute()) {
        $associationId = $stmt->insert_id;
        sendSuccessResponse(["association_id" => $associationId], "Association created successfully", 201);
    } else {
        if ($db->errno == 1062) {
            sendErrorResponse("Registration number already exists", 409);
        }
        sendErrorResponse("Failed to create association: " . $db->error, 500);
    }
} catch (Exception $e) {
    sendErrorResponse("Failed to create association: " . $e->getMessage(), 500);
}
?>

