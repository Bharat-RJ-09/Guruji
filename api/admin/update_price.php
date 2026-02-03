<?php
header("Content-Type: application/json");
include '../../config/db.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) exit;

$data = json_decode(file_get_contents("php://input"));
$key = $data->key . "_price"; // 'standard_price' or 'prime_price'
$val = $data->value;

$stmt = $conn->prepare("UPDATE admin_settings SET setting_value = ? WHERE setting_key = ?");
$stmt->bind_param("ss", $val, $key);

if ($stmt->execute()) echo json_encode(["status" => "success"]);
else echo json_encode(["status" => "error"]);
?>