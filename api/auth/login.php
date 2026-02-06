<?php
// api/auth/login.php
session_start();
header("Content-Type: application/json");

include '../../config/db.php';
include '../../config/telegram.php';

$data = json_decode(file_get_contents("php://input"));
$ip_address = $_SERVER['REMOTE_ADDR'];
$time_now   = date("d M Y, h:i A");

try {
    // 1. Determine Method (Telegram Widget vs Password)
    $user_row = null;
    $login_method = "Password";

    if (isset($data->telegram_user)) {
        $tg = $data->telegram_user;
        $login_method = "Telegram Widget";
        $stmt = $conn->prepare("SELECT * FROM users WHERE telegram_chat_id = ?");
        $stmt->bind_param("s", $tg->id);
    } else if (isset($data->username) && isset($data->password)) {
        $userInput = $data->username;
        $password = $data->password;
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $userInput, $userInput);
    } else {
        throw new Exception("Invalid Data");
    }

    // Execute & Fetch
    if(isset($stmt)){
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) throw new Exception("User not found!");
        $user_row = $result->fetch_assoc();
    }

    // Verify Password (if manual login)
    if ($login_method == "Password" && !password_verify($password, $user_row['password_hash'])) {
        throw new Exception("Incorrect Password!");
    }

    // 2. Success Session
    $_SESSION['user_id'] = $user_row['id'];
    $_SESSION['username'] = $user_row['username'];
    $_SESSION['role'] = $user_row['role'];

    // 3. ✨ PROFESSIONAL LOGIN ALERT
    if (!empty($user_row['telegram_chat_id'])) {
        
        // Format the DB timestamp (created_at) if it exists, else use "Unknown"
        $created_on = isset($user_row['created_at']) ? date("d M Y", strtotime($user_row['created_at'])) : "N/A";
        
        $msg = "🔐 <b>New Login Detected</b>\n\n" .
               "👤 <b>User:</b> {$user_row['full_name']} (@{$user_row['username']})\n" .
               "📅 <b>Login Time:</b> $time_now\n" .
               "🌍 <b>IP Address:</b> $ip_address\n" .
               "🔑 <b>Method:</b> $login_method\n\n" .
               "📜 <b>Account Stats:</b>\n" .
               "🗓 <b>Member Since:</b> $created_on\n\n" .
               "<i>If this wasn't you, please change your password immediately.</i>";
               
        sendTelegramMessage($user_row['telegram_chat_id'], $msg);
    }

    echo json_encode(["status" => "success", "message" => "Login Successful!"]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>