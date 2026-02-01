<?php
// api/auth/signup.php

// 1. Clean Output & Headers
error_reporting(0); 
ini_set('display_errors', 0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

try {
    // 2. Connect DB
    if (!file_exists('../../config/db.php')) {
        throw new Exception("Database config missing");
    }
    include '../../config/db.php';

    // 3. Receive Data
    $data = json_decode(file_get_contents("php://input"));
    if (is_null($data)) { $data = (object) $_POST; } // Fallback

    if(!isset($data->full_name) || !isset($data->username) || !isset($data->email) || !isset($data->password)){
        throw new Exception("All fields are required.");
    }

    $full_name = htmlspecialchars(strip_tags($data->full_name));
    $username = htmlspecialchars(strip_tags($data->username));
    $email = htmlspecialchars(strip_tags($data->email));
    $password = htmlspecialchars(strip_tags($data->password));
    $otp = rand(100000, 999999);

    // 4. Duplicate Check
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0){
        throw new Exception("Username or Email already exists!");
    }
    $check->close();

    // 5. Insert User
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $conn->prepare("INSERT INTO users (full_name, username, email, password_hash, otp, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
    $insert->bind_param("ssssi", $full_name, $username, $email, $password_hash, $otp);

    if($insert->execute()){
        
        // --- EMAIL LOGIC (Safe for Localhost) ---
        $email_status = "Skipped (Localhost)";
        
        // Try sending email only if cURL is enabled
        if (function_exists('curl_init')) {
            $apiKey = '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'; 
            $senderEmail = 'onehp267@gmail.com'; 

            $url = 'https://api.sendgrid.com/v3/mail/send';
            $postData = [
                "personalizations" => [["to" => [["email" => $email]], "subject" => "Your OTP for NextEdu"]],
                "from" => ["email" => $senderEmail, "name" => "NextEdu Team"],
                "content" => [["type" => "text/plain", "value" => "Hello $full_name, Your OTP is: $otp"]]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $apiKey", "Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix for Localhost SSL
            
            $response = curl_exec($ch);
            curl_close($ch);
        }

        // ✅ SUCCESS RESPONSE (With OTP for Devs)
        echo json_encode([
            "status" => "success", 
            "message" => "Account Created!", 
            "debug_otp" => $otp 
        ]);

    } else {
        throw new Exception("Insert Failed: " . $insert->error);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
}

if (isset($conn)) $conn->close();
?>