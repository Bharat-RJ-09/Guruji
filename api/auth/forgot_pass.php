<?php
// api/auth/forgot_pass.php
header("Content-Type: application/json");

// 1. Load Config
include '../../config/db.php';
include '../../config/telegram.php';

$data = json_decode(file_get_contents("php://input"));
$input = $data->email_or_user ?? '';

if(empty($input)) {
    echo json_encode(["status" => "error", "message" => "Please enter your username or email."]);
    exit;
}

// 2. Find User
$stmt = $conn->prepare("SELECT id, username, telegram_chat_id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $input, $input);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    echo json_encode(["status" => "error", "message" => "User not found."]);
    exit;
}

$row = $result->fetch_assoc();

// 3. Check if Telegram is Linked (CRITICAL CHECK)
if(empty($row['telegram_chat_id'])){
    echo json_encode([
        "status" => "error", 
        "message" => "⚠️ This account has no Telegram ID linked! We cannot send you a message. Please create a new account with the updated Signup form."
    ]);
    exit;
}

// 4. Generate OTP & Save to DB
$otp_code = rand(100000, 999999);
$update = $conn->prepare("UPDATE users SET otp = ? WHERE id = ?");
$update->bind_param("ii", $otp_code, $row['id']);

if($update->execute()){
    // 5. Send Message via Bot
    $msg = "🔐 <b>Password Reset</b>\n\n" .
           "Hello <b>{$row['username']}</b>,\n" .
           "Use this code to reset your password:\n\n" .
           "👉 <code>$otp_code</code>\n\n" .
           "<i>If you did not request this, ignore this message.</i>";

    sendTelegramMessage($row['telegram_chat_id'], $msg);

    echo json_encode(["status" => "success", "message" => "OTP sent to your Telegram!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error"]);
}
?>