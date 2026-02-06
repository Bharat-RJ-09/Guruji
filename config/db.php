<?php
// config/db.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle Preflight for API
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 1. Load Secrets
$secretsPath = __DIR__ . '/secrets.php';
if (!file_exists($secretsPath)) {
    die(json_encode(["status" => "error", "message" => "Configuration missing."]));
}
$secrets = include $secretsPath;

// 2. Determine Environment (Local vs Live)
$host = $secrets['db_host'];
$user = $secrets['db_user'];
$pass = $secrets['db_pass'];
$db   = $secrets['db_name'];

// Automatic Localhost Override
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "nextedu_db";
}

// 3. Connect
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable clean error handling

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    // 🔒 Security: Never show $e->getMessage() to the user in production!
    error_log("DB Connection Error: " . $e->getMessage()); // Logs to server file
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "Database Connection Failed. Please try again later."]));
}
?>