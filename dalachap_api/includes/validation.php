<?php
// =============================================
// File: includes/validation.php
// Input Validation Functions
// =============================================

function validateRequired($data, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[$field] = ucfirst($field) . " is required";
        }
    }
    return $errors;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    // Tanzanian phone numbers: 07xxxxxxxx or 06xxxxxxxx or 01xxxxxxxx
    return preg_match('/^[0-9]{10}$|^\+255[0-9]{9}$/', $phone);
}

function validateLatitude($lat) {
    return is_numeric($lat) && $lat >= -90 && $lat <= 90;
}

function validateLongitude($lng) {
    return is_numeric($lng) && $lng >= -180 && $lng <= 180;
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function getJsonInput() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendErrorResponse("Invalid JSON input", 400);
    }
    return $input;
}
?>