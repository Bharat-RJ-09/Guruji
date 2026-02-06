<?php
// api/secure/update_avatar.php
header("Content-Type: application/json");
include '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) exit;

$data = json_decode(file_get_contents("php://input"));
$avatar = $data->avatar;

// Basic validation
$allowed = ['avatar1', 'avatar2', 'avatar3', 'avatar4', 'avatar5', 'avatar6'];
if(!in_array($avatar, $allowed)) {
    echo json_encode(["status" => "error", "message" => "Invalid Avatar"]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
$stmt->bind_param("si", $avatar, $_SESSION['user_id']);

if($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}
?>