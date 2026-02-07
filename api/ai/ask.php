<?php
// api/ai/ask.php
session_start();
header("Content-Type: application/json");

// 1. Load Config & DB
if (!file_exists('../../config/db.php')) {
    echo json_encode(["status" => "error", "message" => "Config missing"]);
    exit;
}
include '../../config/db.php';
$secrets = include '../../config/secrets.php';

// 2. Check Login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Please login to ask questions."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents("php://input"), true);
$question = $input['question'] ?? '';
$context = $input['context'] ?? '';

if (empty($question)) {
    echo json_encode(["status" => "error", "message" => "Please type a question!"]);
    exit;
}

// 3. 🛡️ GET PLAN & DEFINE LIMITS
$stmt = $conn->prepare("SELECT subscription_plan FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_res = $stmt->get_result()->fetch_assoc();
$plan = $user_res['subscription_plan'] ?? 'free'; // Default to free

// 🛑 LIMITS CONFIGURATION
$LIMITS = [
    'free'     => 5,
    'standard' => 30,
    'prime'    => 999999 // Unlimited
];

$daily_limit = $LIMITS[$plan] ?? 5; // Fallback to 5 if plan name is weird

// 4. 📊 CHECK USAGE TODAY
// We count how many rows exist for this user where the date is TODAY
$count_sql = "SELECT COUNT(*) as used FROM ai_usage_logs 
              WHERE user_id = ? AND DATE(requested_at) = CURDATE()";
$c_stmt = $conn->prepare($count_sql);
$c_stmt->bind_param("i", $user_id);
$c_stmt->execute();
$usage_data = $c_stmt->get_result()->fetch_assoc();
$used_today = $usage_data['used'];

// 5. 🚫 ENFORCE LIMIT
if ($used_today >= $daily_limit) {
    
    // Custom message based on plan
    $msg = "🔒 Daily limit reached ($used_today/$daily_limit).";
    if($plan === 'free') $msg .= " Upgrade to Standard for 30 questions!";
    else if($plan === 'standard') $msg .= " Go Prime for UNLIMITED access!";
    
    echo json_encode([
        "status" => "forbidden", 
        "message" => $msg,
        "is_limit" => true
    ]);
    exit;
}

// 6. 🧠 CALL GOOGLE GEMINI API
$apiKey = $secrets['gemini_api_key'];
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

$systemInstruction = "You are 'Guruji', a friendly Indian teaching assistant. 
Explain simply in Hinglish or English. Keep answers concise. 
User Plan: " . ucfirst($plan) . ". Context: $context";

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $systemInstruction . "\n\nStudent Question: " . $question]
            ]
        ]
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 7. HANDLE RESPONSE & LOG USAGE
if ($httpCode === 200) {
    $data = json_decode($response, true);
    $ai_reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, I couldn't think of an answer.";
    
    // ✅ SUCCESS! LOG THIS REQUEST TO DB
    // We only count successful answers against their limit
    $log_stmt = $conn->prepare("INSERT INTO ai_usage_logs (user_id) VALUES (?)");
    $log_stmt->bind_param("i", $user_id);
    $log_stmt->execute();
    
    // Calculate remaining (just for frontend info if needed)
    $remaining = $daily_limit - ($used_today + 1);

    echo json_encode([
        "status" => "success", 
        "reply" => $ai_reply,
        "remaining" => $remaining
    ]);
} else {
    error_log("AI API Error: $response");
    echo json_encode([
        "status" => "error", 
        "message" => "My brain is tired. Please try again in a moment."
    ]);
}
?>