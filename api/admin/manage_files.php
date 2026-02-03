<?php
header("Content-Type: application/json");
session_start();

if (!isset($_SESSION['admin_logged_in'])) exit;

$dir = "../../assets/json/";
$action = isset($_POST['action']) ? $_POST['action'] : '';

// LIST FILES
if ($action === 'list') {
    $files = array_diff(scandir($dir), array('.', '..'));
    echo json_encode(["status" => "success", "files" => array_values($files)]);
    exit;
}

// UPLOAD FILE
if ($action === 'upload') {
    if (!isset($_FILES['file'])) exit(json_encode(["status" => "error", "message" => "No file"]));
    
    $filename = basename($_FILES["file"]["name"]);
    $target = $dir . $filename;
    $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));

    if ($ext !== "json") exit(json_encode(["status" => "error", "message" => "Only JSON allowed"]));

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target)) {
        echo json_encode(["status" => "success", "message" => "File Updated!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Upload Failed"]);
    }
}
?>