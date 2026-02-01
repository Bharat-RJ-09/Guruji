<?php
// config/db.php

// 1. Detect Environment
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    // 🏠 LOCALHOST (XAMPP)
    $servername = "localhost";
    $username = "root";
    $password = ""; // XAMPP default is empty
    $dbname = "nextedu_db"; 
} else {
    // 🌐 LIVE SERVER (InfinityFree)
    $servername = "sql300.infinityfree.com"; 
    $username = "if0_38529899";
    $password = "Xe4JJvRKGhz";
    $dbname = "if0_38529899_nextedu_db"; 
}

// 2. Connect
$conn = new mysqli($servername, $username, $password, $dbname);

// 3. Handle Connection Error (JSON format)
if ($conn->connect_error) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "Database Connection Failed: " . $conn->connect_error]));
}
?>