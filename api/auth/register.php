<?php
// 1. Debugging ON (Errors dikhane ke liye)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. JSON Header (Zaruri hai)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// 3. Database Include (Path check karo!)
// Agar db.php 'config' folder me hai:
if (file_exists('../../config/db.php')) {
    include_once '../../config/db.php';
} 
// Agar db.php bahar (root) me hai:
else if (file_exists('../../db.php')) {
    include_once '../../db.php';
} 
else {
    die(json_encode(["status" => "error", "message" => "Database file not found! Check paths."]));
}

// ⚠️ IMPORTANT: Mailer ko abhi ke liye band kar rahe hain
// include_once '../../utils/mailer.php'; 

// 4. Data Receive
$data = json_decode(file_get_contents("php://input"));

// Agar JSON data nahi aaya to POST check karo (Form Data ke liye)
if (is_null($data)) {
    $data = (object) $_POST;
}

// Validation
if(
    !isset($data->full_name) || 
    !isset($data->username) || 
    !isset($data->email) || 
    !isset($data->password)
){
    echo json_encode(["status" => "error", "message" => "Incomplete Data. Fields missing."]);
    exit;
}

// Variables
$full_name = htmlspecialchars(strip_tags($data->full_name));
$username = htmlspecialchars(strip_tags($data->username));
$email = htmlspecialchars(strip_tags($data->email));
$password = htmlspecialchars(strip_tags($data->password));

// DB Check
if (!isset($conn)) {
    echo json_encode(["status" => "error", "message" => "Database connection failed inside register.php"]);
    exit;
}

// Duplicate Check
$check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$check->bind_param("ss", $username, $email);
$check->execute();
$check->store_result();

if($check->num_rows > 0){
    echo json_encode(["status" => "error", "message" => "Username or Email already exists!"]);
    exit;
}
$check->close();

// Insert User
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$insert = $conn->prepare("INSERT INTO users (full_name, username, email, password_hash, is_verified) VALUES (?, ?, ?, ?, 1)");
// Note: is_verified = 1 kar diya hai direct login ke liye (OTP baad me dekhenge)

$insert->bind_param("ssss", $full_name, $username, $email, $password_hash);

if($insert->execute()){
    echo json_encode(["status" => "success", "message" => "Account Created Successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "DB Insert Error: " . $insert->error]);
}

$conn->close();
?>