<?php
// api/secure/profile.php

// 1. Silent Errors
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/db.php';
ob_clean(); // Clean buffer to prevent JSON crash

// 2. Auth Check
if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Access Denied"]);
    exit;
}

// 3. Fetch User Data (Added 'subscription_plan')
$user_id = $_SESSION['user_id'];
$sql = "SELECT full_name, username, email, role, is_verified, created_at, subscription_plan FROM users WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $user = $result->fetch_assoc();
    
    // Default fallback if column is empty
    if(empty($user['subscription_plan'])) { 
        $user['subscription_plan'] = 'free'; 
    }

    echo json_encode(["status" => "success", "user" => $user]);
} else {
    session_destroy();
    echo json_encode(["status" => "error", "message" => "User not found"]);
}
?>