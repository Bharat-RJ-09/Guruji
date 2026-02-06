<?php
// api/secure/profile.php
session_start();
header("Content-Type: application/json");
include '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ FIX: Added 'subscription_plan' to the SELECT list
$stmt = $conn->prepare("SELECT full_name, username, email, subscription_plan FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // If plan is empty/null, default to 'free'
    if(empty($row['subscription_plan'])) {
        $row['subscription_plan'] = 'free';
    }
    
    echo json_encode(["status" => "success", "user" => $row]);
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}
?>