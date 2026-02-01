<?php
// 1. HTML Errors Band Karo
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// 2. File Path Fix (Server Path)
// __DIR__ se hum 'api/quiz' me hain.
// Ek step peeche jaakar 'data' folder dhoondhenge.
$subject = isset($_GET['subject']) ? $_GET['subject'] : 'english';
$json_file = __DIR__ . "/../data/" . $subject . ".json";

// 3. Check File
if (!file_exists($json_file)) {
    // Agar file nahi mili, to JSON error bhejo (HTML nahi)
    echo json_encode(["status" => "error", "message" => "Question file missing: " . $json_file]);
    exit;
}

// 4. Read Data
$json_data = file_get_contents($json_file);
$all_questions = json_decode($json_data, true);

if (!$all_questions) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON in file"]);
    exit;
}

// 5. Shuffle & Send
shuffle($all_questions);
$selected_questions = array_slice($all_questions, 0, 10);

$frontend_questions = [];
foreach ($selected_questions as $q) {
    $frontend_questions[] = [
        "id" => $q['id'],
        "question_text" => $q['q'],
        "option_a" => $q['a'],
        "option_b" => $q['b'],
        "option_c" => $q['c'],
        "option_d" => $q['d']
    ];
}

echo json_encode(["status" => "success", "data" => $frontend_questions]);
?>