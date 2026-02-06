<?php
// api/ai/grammar.php

// 1. SILENCE ERRORS (Crucial for clean JSON)
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

// 2. Load Config
if (!file_exists('../../config/db.php')) {
    echo json_encode(["status" => "error", "message" => "Config missing"]);
    exit;
}
include '../../config/db.php';
$secrets = include '../../config/secrets.php';

// 3. Auth Check
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Please login."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents("php://input"), true);
$text = $input['text'] ?? '';
$lang = $input['lang'] ?? 'English';

if (empty($text)) {
    echo json_encode(["status" => "error", "message" => "Please enter text."]);
    exit;
}

// 4. CHECK LIMITS (Simplified)
// ... (Keep your limit logic if you want, or skip for testing) ...

// 5. CALL API (Robust)
$apiKey = $secrets['gemini_api_key'];
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

$prompt = "You are an expert Grammar Checker. 
Task: Correct the grammar of the following text in **$lang**.
Output: Provide ONLY the corrected text. Do not add introductions.
Text:
$text";

$payload = [
    "contents" => [
        [
            "parts" => [["text" => $prompt]]
        ]
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
// ✅ FIX 1: Handle Special Characters safely
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 6. HANDLE RESPONSE (Safely)
if ($httpCode !== 200) {
    // Log the actual error for you to see in server logs
    error_log("Gemini API Error ($httpCode): $response");
    echo json_encode(["status" => "error", "message" => "AI is busy or text is too complex. Try shorter text."]);
    exit;
}

$data = json_decode($response, true);

// ✅ FIX 2: Check if 'candidates' actually exists
if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode(["status" => "error", "message" => "Could not correct this text. It might violate safety guidelines."]);
    exit;
}

$corrected_text = $data['candidates'][0]['content']['parts'][0]['text'];

// 7. Log Usage (Optional)
$conn->query("INSERT INTO ai_usage_logs (user_id) VALUES ($user_id)");

echo json_encode([
    "status" => "success", 
    "corrected" => trim($corrected_text)
]);
?>