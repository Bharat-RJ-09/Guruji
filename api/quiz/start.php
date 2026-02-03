<?php
// api/quiz/start.php

error_reporting(0);
ini_set('display_errors', 0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// 1. Get Subject
$subject = isset($_GET['subject']) ? strtolower($_GET['subject']) : 'english';

// 2. Locate JSON File
// Goes back one folder (..) to 'api', then into 'data'
$json_file = __DIR__ . "/../data/" . $subject . ".json";

if (!file_exists($json_file)) {
    echo json_encode(["status" => "error", "message" => "Subject file not found: " . $subject]);
    exit;
}

// 3. Read & Shuffle Data
$json_data = file_get_contents($json_file);
$all_questions = json_decode($json_data, true);

if (!$all_questions) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON format in file"]);
    exit;
}

shuffle($all_questions);
$selected_questions = array_slice($all_questions, 0, 10);

// 4. Send to Frontend (Hide Answer)
$frontend_questions = [];
foreach ($selected_questions as $q) {
    $frontend_questions[] = [
        "id" => $q['id'],
        "question_text" => $q['q'], // JSON uses 'q', Frontend expects 'question_text'
        "option_a" => $q['a'],
        "option_b" => $q['b'],
        "option_c" => $q['c'],
        "option_d" => $q['d']
    ];
}

echo json_encode(["status" => "success", "data" => $frontend_questions]);
?>