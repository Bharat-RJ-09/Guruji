<?php
header("Content-Type: application/json");
include '../../config/db.php';
session_start();

$data = json_decode(file_get_contents("php://input"));
$pass = isset($data->password) ? $data->password : '';

// Fetch Hash
$stmt = $conn->prepare("SELECT setting_value FROM admin_settings WHERE setting_key = 'admin_password'");
$stmt->execute();
$hash = $stmt->get_result()->fetch_assoc()['setting_value'];

// Verify
if (password_verify($pass, $hash)) {
    $_SESSION['admin_logged_in'] = true;
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Access Denied"]);
}
?>