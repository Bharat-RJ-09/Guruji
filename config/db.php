<?php
// config/db.php

// Include secrets (Suppress error if missing to avoid leaking paths)
@include_once __DIR__ . '/secrets.php';

// Fallback defaults (Empty) if secrets.php is missing
if (!defined('DB_SERVER_LOCAL')) {
    die(json_encode(["status" => "error", "message" => "Configuration missing."]));
}

// Detect Environment
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    $servername = DB_SERVER_LOCAL;
    $username   = DB_USER_LOCAL;
    $password   = DB_PASS_LOCAL;
    $dbname     = DB_NAME_LOCAL;
} else {
    $servername = DB_SERVER_LIVE;
    $username   = DB_USER_LIVE;
    $password   = DB_PASS_LIVE;
    $dbname     = DB_NAME_LIVE;
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "Connection Failed"]));
}
?>