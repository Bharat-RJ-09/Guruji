<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if(!isset($data->email) || !isset($data->otp)){
    echo json_encode(["status" => "error", "message" => "Email and OTP required."]);
    exit;
}

$email = htmlspecialchars(strip_tags($data->email));
$otp = htmlspecialchars(strip_tags($data->otp));
$now = date("Y-m-d H:i:s");

// 1. Check karo OTP sahi hai aur expire nahi hua
$check_sql = "SELECT id FROM password_resets WHERE email = ? AND token = ? AND expires_at > ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("sss", $email, $otp, $now);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0){
    
    // 2. User ko Verified mark karo
    $update_sql = "UPDATE users SET is_verified = 1 WHERE email = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("s", $email);
    
    if($update_stmt->execute()){
        // 3. OTP use ho gaya, ab delete kar do
        $del_sql = "DELETE FROM password_resets WHERE email = ?";
        $del_stmt = $conn->prepare($del_sql);
        $del_stmt->bind_param("s", $email);
        $del_stmt->execute();

        echo json_encode(["status" => "success", "message" => "Account Verified! You can now login."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Verification failed."]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid or Expired OTP."]);
}
?>