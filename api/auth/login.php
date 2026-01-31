<?php
// Session Start (Login yaad rakhne ke liye)
session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if(!isset($data->login_id) || !isset($data->password)){
    echo json_encode(["status" => "error", "message" => "Please enter Username/Email and Password."]);
    exit;
}

$login_id = htmlspecialchars(strip_tags($data->login_id)); // Email ya Username
$password = htmlspecialchars(strip_tags($data->password));

// 1. User dhundo (Email ya Username dono se login allow hai)
$sql = "SELECT id, full_name, username, email, password_hash, is_verified, role FROM users WHERE email = ? OR username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $login_id, $login_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $row = $result->fetch_assoc();

    // 2. Check Verification Status
    if($row['is_verified'] == 0){
        echo json_encode(["status" => "error", "message" => "Account not verified! Please verify OTP sent to email."]);
        exit;
    }

    // 3. Verify Password
    if(password_verify($password, $row['password_hash'])){
        
        // 4. Set Session Variables (Server side secure login)
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        echo json_encode([
            "status" => "success", 
            "message" => "Login Successful!",
            "redirect" => "dashboard.html",
            "user" => [
                "full_name" => $row['full_name'],
                "role" => $row['role']
            ]
        ]);

    } else {
        echo json_encode(["status" => "error", "message" => "Invalid Password."]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "User not found."]);
}
?>