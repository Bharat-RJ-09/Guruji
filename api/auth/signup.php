<?php
// 1. Errors Hide karo (Taaki JSON na toote)
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// 2. Database Connection (InfinityFree wala sahi code)
$servername = "sql300.infinityfree.com"; 
$username = "if0_38529899";
// 👇 Yahan apna Database Password dalo
$password = "Xe4JJvRKGhz"; 
// 👇 Yahan apna Sahi Database Name dalo
$dbname = "if0_38529899_nextedu"; 

$conn = @new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB Error: " . $conn->connect_error]);
    exit;
}

// 3. Data Receive
$data = json_decode(file_get_contents("php://input"));
if (is_null($data)) {
    $data = (object) $_POST;
}

// 4. Validation
if(!isset($data->full_name) || !isset($data->username) || !isset($data->email) || !isset($data->password)){
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

$full_name = htmlspecialchars(strip_tags($data->full_name));
$username = htmlspecialchars(strip_tags($data->username));
$email = htmlspecialchars(strip_tags($data->email));
$password = htmlspecialchars(strip_tags($data->password));
$otp = rand(100000, 999999); // 6 Digit ka OTP Generate karo

// 5. Duplicate Check
$check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$check->bind_param("ss", $username, $email);
$check->execute();
$check->store_result();

if($check->num_rows > 0){
    echo json_encode(["status" => "error", "message" => "Username or Email already exists!"]);
    exit;
}
$check->close();

// 6. Insert User (OTP ke saath)
$password_hash = password_hash($password, PASSWORD_DEFAULT);
// Note: 'is_verified' ko 0 set kiya hai
$insert = $conn->prepare("INSERT INTO users (full_name, username, email, password_hash, otp, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
$insert->bind_param("ssssi", $full_name, $username, $email, $password_hash, $otp);

if($insert->execute()){

    // --- 🚀 SENDGRID EMAIL LOGIC START ---
    
    // 👇 STEP 1: Yahan apni wo Lambi API Key paste karo
    $apiKey = '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'; 
    
    // 👇 STEP 2: Yahan apna SendGrid Verified Email likho
    $senderEmail = 'onehp267@gmail.com'; 

    $url = 'https://api.sendgrid.com/v3/mail/send';
    $postData = [
        "personalizations" => [[
            "to" => [["email" => $email]],
            "subject" => "Your OTP for NextEdu"
        ]],
        "from" => ["email" => $senderEmail, "name" => "NextEdu Team"],
        "content" => [[
            "type" => "text/plain",
            "value" => "Hello $full_name,\n\nWelcome to NextEdu! Your OTP is: $otp\n\nPlease enter this code to verify your account."
        ]]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    // --- SENDGRID LOGIC END ---

    echo json_encode(["status" => "success", "message" => "OTP Sent to $email! Check inbox."]);

} else {
    echo json_encode(["status" => "error", "message" => "Register Failed: " . $insert->error]);
}

$conn->close();
?>