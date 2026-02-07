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

// ====================================================
// 1. Database Configuration (Simple Mode)
// ====================================================

// A. LIVE SERVER DETAILS (Fill this for your hosting)
$host = "sql300.infinityfree.com";           // Usually 'localhost' on most hosting providers
$user = "if0_38529899";  // Your hosting database username
$pass = "Xe4JJvRKGhz";  // Your hosting database password
$db   = "if0_38529899_nextedu_db";   // Your hosting database name

// B. LOCALHOST OVERRIDE (For testing on your PC/XAMPP)
// This automatically overwrites the above settings if running locally.
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "nextedu_db"; // Make sure this matches your local PHPMyAdmin DB
}

// ====================================================
// 2. Connect to Database
// ====================================================
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable clean error handling

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    // Log the actual error to the server file, but show a safe message to the user
    error_log("DB Connection Error: " . $e->getMessage()); 
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "Database Connection Failed."]));
}
?>