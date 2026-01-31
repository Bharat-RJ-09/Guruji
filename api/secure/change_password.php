<?php
session_start();
header("Content-Type: application/json");
include_once '../../config/db.php';

if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if(!isset($data->current_pass) || !isset($data->new_pass)){
    echo json_encode(["status" => "error", "message" => "All fields required"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$current_pass = $data->current_pass;
$new_pass = $data->new_pass;

// 1. Fetch Old Password Hash
$sql = "SELECT password_hash FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// 2. Verify Old Password
if(!password_verify($current_pass, $row['password_hash'])){
    echo json_encode(["status" => "error", "message" => "Incorrect Current Password"]);
    exit;
}

// 3. Update New Password
if(strlen($new_pass) < 6){
    echo json_encode(["status" => "error", "message" => "New password too short (Min 6 chars)"]);
    exit;
}

$new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
$update_sql = "UPDATE users SET password_hash = ? WHERE id = ?";
$stmt2 = $conn->prepare($update_sql);
$stmt2->bind_param("si", $new_hash, $user_id);

if($stmt2->execute()){
    echo json_encode(["status" => "success", "message" => "Password Changed Successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Server Error"]);
}
?>