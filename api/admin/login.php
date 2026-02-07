<?php
// api/admin/login.php
session_start();
header("Content-Type: application/json");

// 1. Load Configurations
// Adjust paths if necessary based on your folder structure
if(file_exists('../../config/db.php')) include '../../config/db.php';
if(file_exists('../../config/telegram.php')) include '../../config/telegram.php';

$data = json_decode(file_get_contents("php://input"), true);
$user = $data['username'] ?? '';
$pass = $data['password'] ?? '';

// 2. Security Check (Hardcoded for Setup, move to DB later)
$VALID_USER = "admin";
$VALID_PASS = "admin123"; // ⚠️ CHANGE THIS IN PRODUCTION

if ($user === $VALID_USER && $pass === $VALID_PASS) {
    
    // 3. Generate 6-Digit OTP
    $otp = rand(100000, 999999);
    $_SESSION['admin_temp_otp'] = $otp; // Store temporarily
    
    // 4. Send to Telegram
    // We use the function from config/telegram.php
    if (defined('ADMIN_CHAT_ID')) {
        $msg = "🔐 <b>Admin Login Request</b>\n\n" .
               "Code: <code>$otp</code>\n" .
               "<i>Do not share this code.</i>";
        
        // Pass 'true' to use the Payment/Admin Bot
        sendTelegramMessage(ADMIN_CHAT_ID, $msg, true);
        
        echo json_encode(["status" => "otp_required", "message" => "OTP Sent to Telegram"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Telegram Config Missing (ADMIN_CHAT_ID)"]);
    }

} else {
    // Wrong Password
    echo json_encode(["status" => "error", "message" => "Invalid Credentials"]);
}
?>