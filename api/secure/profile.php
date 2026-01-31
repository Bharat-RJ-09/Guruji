<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 1. SECURITY CHECK: Kya user logged in hai?
if(!isset($_SESSION['user_id'])){
    http_response_code(401); // Unauthorized Code
    echo json_encode(["status" => "error", "message" => "Access Denied. Please Login."]);
    exit;
}

include_once '../../config/db.php';

// 2. User ka latest data fetch karo (Password chhod ke)
$user_id = $_SESSION['user_id'];
$sql = "SELECT full_name, username, email, role, is_verified, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $user = $result->fetch_assoc();
    echo json_encode(["status" => "success", "user" => $user]);
} else {
    // Agar session hai par user DB se udd gaya (Rare case)
    session_destroy();
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "User not found."]);
}
?>