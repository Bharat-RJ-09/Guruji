<?php
// api/ai/ask.php
header("Content-Type: application/json");
include '../../config/db.php';
session_start();

// 1. Auth Check
if(!isset($_SESSION['user_id'])){
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"));
$user_question = isset($data->question) ? trim($data->question) : '';

if(empty($user_question)){
    echo json_encode(["status" => "error", "message" => "Question cannot be empty"]);
    exit;
}

// 2. Subscription Check (Prime Only)
$stmt = $conn->prepare("SELECT subscription_plan FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if($user['subscription_plan'] !== 'prime'){
    echo json_encode([
        "status" => "forbidden", 
        "message" => "🔒 Upgrade to Prime Batch to use AI!"
    ]);
    exit;
}

// 3. API SETUP (Updated to gemini-2.5-flash)
// 👇 PASTE KEY CAREFULLY INSIDE QUOTES 👇
$rawKey = "AIzaSyCgbVNWVMQCGBuR7Y1XjmnTzdMleJ8ho5A"; 
$apiKey = trim($rawKey); 

// ✅ CHANGED MODEL HERE:
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => "You are a helpful AI Tutor. Keep answers short and simple. Student asks: " . $user_question]
            ]
        ]
    ]
];

// 4. Send Request
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
// SSL Fix
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo json_encode(["status" => "error", "message" => "Connection Error: " . curl_error($ch)]);
    curl_close($ch);
    exit;
}
curl_close($ch);

$result = json_decode($response, true);

// 5. Check Response
if(isset($result['candidates'][0]['content']['parts'][0]['text'])){
    $ai_reply = $result['candidates'][0]['content']['parts'][0]['text'];
    
    // Formatting: Replace **bold** with HTML <b>
    $formatted_reply = preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $ai_reply);
    
    echo json_encode(["status" => "success", "reply" => $formatted_reply]);
} else {
    // Detailed Error
    $error_msg = isset($result['error']['message']) ? $result['error']['message'] : "Unknown Error";
    echo json_encode(["status" => "error", "message" => "Google Error: " . $error_msg]);
}
?>