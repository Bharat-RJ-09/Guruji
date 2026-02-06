<?php
// api/auth/register.php

// 1. Config & Settings
ini_set('display_errors', 0);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/db.php';
include_once '../../config/telegram.php'; // ✅ Added Telegram Config

// 2. Receive Data
$data = json_decode(file_get_contents("php://input"));
if (is_null($data)) $data = (object) $_POST;

// 3. Validation
if(
    !isset($data->full_name) || 
    !isset($data->username) || 
    !isset($data->email) || 
    !isset($data->password)
){
    echo json_encode(["status" => "error", "message" => "Please fill all fields."]);
    exit;
}

$full_name = htmlspecialchars(strip_tags($data->full_name));
$username  = htmlspecialchars(strip_tags($data->username));
$email     = htmlspecialchars(strip_tags($data->email));
$password  = htmlspecialchars(strip_tags($data->password));
$tg_chat_id = isset($data->telegram_chat_id) ? htmlspecialchars(strip_tags($data->telegram_chat_id)) : NULL;

// 4. Capture Metadata (Professional Details)
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT']; // Browser info
$date_now   = date("d M Y, h:i A"); // e.g., 22 Feb 2025, 10:30 PM

try {
    // Check Duplicates
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    if($check->get_result()->num_rows > 0){
        echo json_encode(["status" => "error", "message" => "Username or Email already exists!"]);
        exit;
    }
    $check->close();

    // Insert User
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    // Note: 'created_at' is usually auto-filled by DB, but we capture it for the message
    $insert = $conn->prepare("INSERT INTO users (full_name, username, email, password_hash, telegram_chat_id, is_verified) VALUES (?, ?, ?, ?, ?, 1)");
    $insert->bind_param("sssss", $full_name, $username, $email, $password_hash, $tg_chat_id);

    if($insert->execute()){
        
        // ✨ PROFESSIONAL WELCOME MESSAGE
        if($tg_chat_id) {
            $msg = "🚀 <b>Welcome to NextEdu!</b>\n\n" .
                   "Hello <b>$full_name</b>, your account has been successfully created.\n\n" .
                   "📋 <b>Account Details:</b>\n" .
                   "👤 <b>Username:</b> @$username\n" .
                   "📅 <b>Created On:</b> $date_now\n" .
                   "📧 <b>Email:</b> $email\n\n" .
                   "🔒 <b>Security Info:</b>\n" .
                   "🌍 <b>IP Address:</b> $ip_address\n" .
                   "📱 <b>Device:</b> <i>Standard Web</i>\n\n" .
                   "<i>You can now log in and start learning!</i>";
            
            sendTelegramMessage($tg_chat_id, $msg);
        }

        echo json_encode(["status" => "success", "message" => "Account Created!"]);
    } else {
        throw new Exception("DB Error");
    }

} catch (Exception $e) {
    error_log("Register Error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server Error"]);
}
?>