<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

include '../../config/db.php';

$data = json_decode(file_get_contents("php://input"));

try {
    $full_name = "";
    $username = "";
    $email = "";
    $telegram_id = "";
    $password_hash = "";

    // A. TELEGRAM WIDGET SIGNUP
    if (isset($data->is_telegram_auth)) {
        $full_name = $data->full_name;
        $username = $data->username;
        $telegram_id = $data->telegram_chat_id;
        $email = "tg_" . $telegram_id . "@placeholder.com"; 
        $password_hash = password_hash(rand(100000,999999), PASSWORD_DEFAULT);
    } 
    // B. MANUAL SIGNUP
    else {
        // 1. Password Check
        if ($data->password !== $data->confirm_password) {
            throw new Exception("Passwords do not match!");
        }
        
        // 2. Strict Chat ID Check
        if (empty($data->telegram_chat_id) || !is_numeric($data->telegram_chat_id)) {
            throw new Exception("Valid Telegram Chat ID is REQUIRED!");
        }

        $full_name = htmlspecialchars(strip_tags($data->full_name));
        $username = htmlspecialchars(strip_tags($data->username));
        $email = htmlspecialchars(strip_tags($data->email));
        $telegram_id = $data->telegram_chat_id;
        $password_hash = password_hash($data->password, PASSWORD_DEFAULT);
    }

    // 3. Duplicate Check (Now checking Telegram ID strictly)
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR telegram_chat_id = ?");
    $check->bind_param("sss", $username, $email, $telegram_id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        throw new Exception("User already exists! (Username, Email, or Chat ID is taken)");
    }

    // 4. Insert
    $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password_hash, telegram_chat_id, is_verified) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssss", $full_name, $username, $email, $password_hash, $telegram_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Account Created!"]);
    } else {
        throw new Exception("DB Error: " . $stmt->error);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>