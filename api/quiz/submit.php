<?php
// api/quiz/submit.php

error_reporting(0);
ini_set('display_errors', 0);
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include '../../config/db.php'; 

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

// 2. Receive Data
$input = json_decode(file_get_contents("php://input"), true);
$subject = $input['subject'] ?? 'english';
$user_answers = $input['answers'] ?? [];
$time_taken = isset($input['time_taken']) ? (int)$input['time_taken'] : 0; // Get Time

// 3. Load Correct Answers from JSON
$json_file = __DIR__ . "/../data/" . $subject . ".json";

if (!file_exists($json_file)) {
    echo json_encode(["status" => "error", "message" => "Subject data missing"]);
    exit;
}

$all_questions = json_decode(file_get_contents($json_file), true);
$map = [];
foreach ($all_questions as $q) {
    $map[$q['id']] = $q['ans']; // Map ID -> Correct Answer
}

// 4. Calculate Score
$score = 0;
$total = count($user_answers); // Total answered, or use count($all_questions) for accuracy

foreach ($user_answers as $q_id => $user_opt) {
    if (isset($map[$q_id]) && $map[$q_id] == $user_opt) {
        $score++;
    }
}

// 5. Save to Database (ONLY ONE TIME)
$user_id = $_SESSION['user_id'];

// Make sure your database table 'quiz_history' has the 'time_taken' column!
$stmt = $conn->prepare("INSERT INTO quiz_history (user_id, subject, score, total_questions, time_taken) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("isiii", $user_id, $subject, $score, $total, $time_taken);

if($stmt->execute()){
    echo json_encode([
        "status" => "success", 
        "score" => $score, 
        "total" => $total,
        "time" => $time_taken
    ]);
} else {
    // If this fails, it usually means the 'time_taken' column is missing in DB
    echo json_encode(["status" => "error", "message" => "DB Error: " . $conn->error]);
}
?>