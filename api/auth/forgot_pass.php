<?php
// api/auth/forgot_pass.php
header("Content-Type: application/json");
include '../../config/db.php';

$data = json_decode(file_get_contents("php://input"));
$email = isset($data->email) ? trim($data->email) : '';

if(empty($email)) {
    echo json_encode(["status" => "error", "message" => "Enter your email"]);
    exit;
}

// 1. Check if email exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows === 0){
    // Security: Don't reveal if email exists or not, but for now we say sent
    echo json_encode(["status" => "success", "message" => "If this email exists, a reset link has been sent.", "dev_link" => ""]);
    exit;
}

// 2. Generate Secure Token
$token = bin2hex(random_bytes(32)); // 64 char random string
$expiry = date("Y-m-d H:i:s", strtotime('+15 minutes')); // Expires in 15 mins

// 3. Save to DB
$update = $conn->prepare("UPDATE users SET reset_token = ?, token_expire = ? WHERE email = ?");
$update->bind_param("sss", $token, $expiry, $email);

if($update->execute()){
    // 4. Create Link
    // CHANGE 'localhost/guruji' TO YOUR ACTUAL WEBSITE URL
    $resetLink = "http://localhost/guruji/reset-password.html?token=" . $token;

    // In a real app, you use mail($email, "Reset Password", $msg);
    // Since we are on Free Hosting, we return the link to the frontend to test.
    echo json_encode([
        "status" => "success", 
        "message" => "Reset Link Generated! (Check Console/Alert for Link)",
        "debug_link" => $resetLink 
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error"]);
}
?>