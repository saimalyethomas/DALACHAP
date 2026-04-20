<?php
// =============================================
// File: includes/response.php
// Standardized JSON Response Helper
// =============================================

function sendSuccessResponse($data = [], $message = "Success", $code = 200) {
    http_response_code($code);
    echo json_encode([
        "success" => true,
        "message" => $message,
        "data" => $data,
        "timestamp" => date('Y-m-d H:i:s')
    ]);
    exit();
}

function sendErrorResponse($message, $code = 400, $errors = []) {
    http_response_code($code);
    echo json_encode([
        "success" => false,
        "message" => $message,
        "errors" => $errors,
        "timestamp" => date('Y-m-d H:i:s')
    ]);
    exit();
}

function sendValidationError($errors) {
    sendErrorResponse("Validation failed", 422, $errors);
}
?>