<?php
// api/auth/signup.php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

include '../../config/db.php';
include '../../config/telegram.php';

$data = json_decode(file_get_contents("php://input"));

try {
    $full_name = "";
    $username = "";
    $email = "";
    $telegram_id = "";
    $password_hash = "";

    // 1. Validate Input
    if (isset($data->is_telegram_auth)) {
        // Widget Signup
        $full_name = $data->full_name;
        $username = $data->username;
        $telegram_id = $data->telegram_chat_id;
        $email = "tg_" . $telegram_id . "@nextedu.com"; // Placeholder email
        $password_hash = password_hash(rand(100000,999999), PASSWORD_DEFAULT);
    } else {
        // Manual Signup
        if ($data->password !== $data->confirm_password) {
            throw new Exception("Passwords do not match!");
        }
        if (empty($data->telegram_chat_id) || !is_numeric($data->telegram_chat_id)) {
            throw new Exception("Valid Telegram Chat ID is REQUIRED!");
        }

        $full_name = htmlspecialchars(strip_tags($data->full_name));
        $username = htmlspecialchars(strip_tags($data->username));
        $email = htmlspecialchars(strip_tags($data->email));
        $telegram_id = $data->telegram_chat_id;
        $password_hash = password_hash($data->password, PASSWORD_DEFAULT);
    }

    // 2. Check Duplicates
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR telegram_chat_id = ?");
    $check->bind_param("sss", $username, $email, $telegram_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        throw new Exception("User already exists! (Username, Email, or Chat ID taken)");
    }

    // 3. Insert into Database
    $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password_hash, telegram_chat_id, is_verified) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssss", $full_name, $username, $email, $password_hash, $telegram_id);

    if ($stmt->execute()) {
        
        // 🚀 SEND WELCOME MESSAGE
        $msg = "🎉 <b>Welcome to NextEdu!</b>\n\n" .
               "Hello <b>$full_name</b>,\n" .
               "Your account has been successfully created.\n\n" .
               "✅ <b>Username:</b> $username\n" .
               "📡 <b>Status:</b> Connected for Alerts\n\n" .
               "<i>You will receive login alerts and OTPs here.</i>";
        
        sendTelegramMessage($telegram_id, $msg);

        echo json_encode(["status" => "success", "message" => "Account Created! Check Telegram."]);
    } else {
        throw new Exception("Database Error");
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>