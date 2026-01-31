<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

// Database Connect (Path check karlena)
include '../../config/db.php'; 
// Agar file bahar hai to: include '../../db.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->otp)) {
    echo json_encode(["status" => "error", "message" => "Email and OTP required"]);
    exit;
}

$email = $data->email;
$user_otp = $data->otp;

// 1. Check OTP in Database
$sql = "SELECT id, otp FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit;
}

$row = $result->fetch_assoc();

// 2. Match OTP
if ($row['otp'] == $user_otp) {
    // Sahi hai! Verify kar do (is_verified = 1) aur OTP hata do
    $update = $conn->prepare("UPDATE users SET is_verified = 1, otp = NULL WHERE email = ?");
    $update->bind_param("s", $email);
    $update->execute();
    
    // Auto Login ke liye Session Start kar sakte ho yahan (Optional)
    session_start();
    $_SESSION['user_id'] = $row['id'];

    echo json_encode(["status" => "success", "message" => "Account Verified!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid OTP! Try again."]);
}
?>