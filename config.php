<?php
/**
 * Saint Michael Technical High School
 * Database Connection & Environment Configuration
 * File: config.php
 */

// 1. Database Credentials (Adjust for your XAMPP or production hosting)
define('DB_HOST', 'localhost');
define('DB_NAME', 'smths_school_db');
define('DB_USER', 'root');      // Default XAMPP username is 'root'
define('DB_PASS', '');          // Default XAMPP password is empty ''
define('DB_CHARSET', 'utf8mb4');

// 2. CORS Headers (Allows front-end JavaScript to communicate with PHP)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 3. PDO Connection
function getDbConnection() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error'   => 'Database connection error: ' . $e->getMessage()
            ]);
            exit();
        }
    }
    return $pdo;
}

// 4. Helper Function: JSON Response
function sendJsonResponse($success, $data = [], $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode(array_merge([
        'success'   => $success,
        'message'   => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ], $data));
    exit();
}
?>