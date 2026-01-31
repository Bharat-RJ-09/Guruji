<?php
session_start();
header("Content-Type: application/json");
include_once '../../config/db.php';

if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if(!isset($data->full_name) || empty($data->full_name)){
    echo json_encode(["status" => "error", "message" => "Name cannot be empty"]);
    exit;
}

$new_name = htmlspecialchars(strip_tags($data->full_name));
$user_id = $_SESSION['user_id'];

$sql = "UPDATE users SET full_name = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $new_name, $user_id);

if($stmt->execute()){
    echo json_encode(["status" => "success", "message" => "Profile Updated!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Update Failed"]);
}
?>