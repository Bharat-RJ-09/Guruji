<?php
// 1. Session Start (Login yaad rakhne ke liye)
session_start();

// 2. Error Reporting Off (Clean JSON)
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// 3. Database Connection
// Path check karlena (agar config folder me hai to ../../config/db.php)
include '../../config/db.php'; 

// 4. Data Receive
$data = json_decode(file_get_contents("php://input"));
if (is_null($data)) {
    $data = (object) $_POST;
}

if(!isset($data->username) || !isset($data->password)){
    echo json_encode(["status" => "error", "message" => "Username/Email and Password required"]);
    exit;
}

$userInput = $data->username; // Ye Username ya Email kuch bhi ho sakta hai
$password = $data->password;

// 5. User Dhoondo (Username YA Email se)
$sql = "SELECT id, full_name, username, password_hash, is_verified, role FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $userInput, $userInput);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    echo json_encode(["status" => "error", "message" => "User not found!"]);
    exit;
}

$row = $result->fetch_assoc();

// 6. Password Match Karo
if(password_verify($password, $row['password_hash'])){
    
    // 7. Check: Kya Account Verified Hai?
    if($row['is_verified'] == 0){
        echo json_encode([
            "status" => "error", 
            "message" => "Account not verified! Please verify OTP first.",
            "redirect" => "verify.html?email=" . $userInput // Redirect hint
        ]);
        exit;
    }

    // ✅ SAB SAHI HAI - SESSION START
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['full_name'] = $row['full_name'];
    $_SESSION['role'] = $row['role']; // Admin ya Student

    echo json_encode([
        "status" => "success", 
        "message" => "Login Successful!",
        "user" => [
            "name" => $row['full_name'],
            "role" => $row['role']
        ]
    ]);

} else {
    echo json_encode(["status" => "error", "message" => "Incorrect Password!"]);
}

$conn->close();
?>