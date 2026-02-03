<?php
// api/auth/reset_pass.php
header("Content-Type: application/json");
include '../../config/db.php';

$data = json_decode(file_get_contents("php://input"));
$token = isset($data->token) ? $data->token : '';
$new_pass = isset($data->password) ? trim($data->password) : '';

if(empty($token) || empty($new_pass) || strlen($new_pass) < 6){
    echo json_encode(["status" => "error", "message" => "Invalid Data or Password too short"]);
    exit;
}

// 1. Verify Token & Expiry
$now = date("Y-m-d H:i:s");
$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND token_expire > ?");
$stmt->bind_param("ss", $token, $now);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows === 0){
    echo json_encode(["status" => "error", "message" => "Invalid or Expired Link"]);
    exit;
}

// 2. Update Password & Clear Token
$user_id = $res->fetch_assoc()['id'];
$hash = password_hash($new_pass, PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expire = NULL WHERE id = ?");
$update->bind_param("si", $hash, $user_id);

if($update->execute()){
    echo json_encode(["status" => "success", "message" => "Password Reset Successfully! Login now."]);
} else {
    echo json_encode(["status" => "error", "message" => "Server Error"]);
}
?>