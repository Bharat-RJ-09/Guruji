<?php
// api/auth/reset_pass.php
header("Content-Type: application/json");
include '../../config/db.php';

$data = json_decode(file_get_contents("php://input"));

$username = $data->username ?? '';
$otp      = $data->otp ?? '';
$new_pass = $data->new_password ?? '';

if(empty($username) || empty($otp) || empty($new_pass)){
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// 1. Verify OTP matches User
$stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND otp = ?");
$stmt->bind_param("ssi", $username, $username, $otp);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows === 0){
    echo json_encode(["status" => "error", "message" => "Invalid OTP or User."]);
    exit;
}

$user_id = $res->fetch_assoc()['id'];

// 2. Update Password & Clear OTP
$hash = password_hash($new_pass, PASSWORD_DEFAULT);

// We set OTP to 0 or NULL to invalidate it after use
$update = $conn->prepare("UPDATE users SET password_hash = ?, otp = 0 WHERE id = ?");
$update->bind_param("si", $hash, $user_id);

if($update->execute()){
    echo json_encode(["status" => "success", "message" => "Password Changed! Login now."]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error"]);
}
?>