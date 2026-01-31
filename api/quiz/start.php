<?php
session_start();
// CORS Error hatane ke liye
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Agar login nahi hai to error do
if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Please Login First."]);
    exit;
}

$subject = isset($_GET['subject']) ? $_GET['subject'] : 'english';

// File Path (Dhyan se check karna folder structure)
$json_file = "../data/" . $subject . ".json";

if (!file_exists($json_file)) {
    echo json_encode(["status" => "error", "message" => "Question file not found for $subject"]);
    exit;
}

// File Read
$json_data = file_get_contents($json_file);
$all_questions = json_decode($json_data, true);

if (!$all_questions) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON format"]);
    exit;
}

// Shuffle & Select 10
shuffle($all_questions);
$selected_questions = array_slice($all_questions, 0, 10);

// Answers Hatao (Security)
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