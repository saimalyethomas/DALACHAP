<?php
// =============================================
// File: config/db_connection.php
// Database Connection Class
// =============================================

class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "Theresia@123";
    private $database = "dalachap_db";
    private $connection;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);

        if ($this->connection->connect_error) {
            $this->sendErrorResponse("Database connection failed: " . $this->connection->connect_error, 500);
        }

        $this->connection->set_charset("utf8mb4");
    }

    public function getConnection() {
        return $this->connection;
    }

    // Execute query (INSERT, UPDATE, DELETE)
    public function executeQuery($sql, $params = [], $types = "") {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            return ["error" => $this->connection->error];
        }

        if (!empty($params)) {
            if (empty($types)) {
                $types = str_repeat("s", count($params));
            }
            $stmt->bind_param($types, ...$params);
        }

        $result = $stmt->execute();
        
        if ($result) {
            return [
                "success" => true,
                "insert_id" => $stmt->insert_id,
                "affected_rows" => $stmt->affected_rows
            ];
        } else {
            return ["error" => $stmt->error];
        }
    }

    // Fetch single row
    public function fetchOne($sql, $params = [], $types = "") {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            return null;
        }

        if (!empty($params)) {
            if (empty($types)) {
                $types = str_repeat("s", count($params));
            }
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    // Fetch all rows
    public function fetchAll($sql, $params = [], $types = "") {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            return [];
        }

        if (!empty($params)) {
            if (empty($types)) {
                $types = str_repeat("s", count($params));
            }
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        
        return $rows;
    }

    // Escape string
    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }

    // Get last insert ID
    public function lastInsertId() {
        return $this->connection->insert_id;
    }

    // Close connection
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    private function sendErrorResponse($message, $code = 400) {
        http_response_code($code);
        echo json_encode(["success" => false, "message" => $message]);
        exit();
    }
}

// Create global instance
$database = new Database();
$db = $database->getConnection();
?>
