<?php
// api/admin/verify_otp.php
session_start();
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$input_otp = $data['otp'] ?? '';

// Check if OTP was generated
if (!isset($_SESSION['admin_temp_otp'])) {
    echo json_encode(["status" => "error", "message" => "Session Expired. Login Again."]);
    exit;
}

// Validate
if ($input_otp == $_SESSION['admin_temp_otp']) {
    
    // 🎉 ACCESS GRANTED
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_role'] = 'super_admin';
    
    // Clear temp OTP
    unset($_SESSION['admin_temp_otp']);
    
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Wrong OTP! Access Denied."]);
}
?>