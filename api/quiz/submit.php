<?php
// api/quiz/submit.php

// 1. Silent Errors (JSON fix)
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 2. DB Connection
if (!file_exists('../../config/db.php')) {
    echo json_encode(["status" => "error", "message" => "DB Config Missing"]);
    exit;
}
include '../../config/db.php';

// 3. Login Check
if(!isset($_SESSION['user_id'])){
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

// 4. Receive Data
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!isset($data['subject']) || !isset($data['answers'])) {
    echo json_encode(["status" => "error", "message" => "Invalid Data Received"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$subject = $data['subject'];
$user_answers = $data['answers'];

// 5. Load Questions
$json_file = "../data/" . $subject . ".json";
if (!file_exists($json_file)) {
    echo json_encode(["status" => "error", "message" => "Subject Data Not Found"]);
    exit;
}

$all_questions = json_decode(file_get_contents($json_file), true);
if (!$all_questions) {
    echo json_encode(["status" => "error", "message" => "Question File Corrupted"]);
    exit;
}

// 6. Map Answers (ID => Answer)
$question_map = [];
foreach ($all_questions as $q) {
    $question_map[$q['id']] = $q['ans'];
}

// 7. Calculate Score
$score = 0;
$total = count($user_answers);

foreach($user_answers as $q_id => $selected_opt) {
    if (isset($question_map[$q_id]) && $question_map[$q_id] === $selected_opt) {
        $score++;
    }
}

// 8. Save to DB
$save_sql = "INSERT INTO quiz_history (user_id, subject, score, total_questions) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($save_sql);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "DB Prepare Failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("isii", $user_id, $subject, $score, $total);

if($stmt->execute()){
    echo json_encode([
        "status" => "success", 
        "score" => $score, 
        "total" => $total,
        "message" => "Quiz Submitted Successfully!"
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "DB Save Failed: " . $stmt->error]);
}

$conn->close();
?>