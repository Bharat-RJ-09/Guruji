<?php
session_start();
header("Content-Type: application/json");
include_once '../../config/db.php';

if(!isset($_SESSION['user_id'])){
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
// Data format: { "subject": "gk", "answers": { "1": "a", "2": "c" ... } }

$user_id = $_SESSION['user_id'];
$subject = $data['subject'];
$user_answers = $data['answers'];
$score = 0;
$total = count($user_answers);

// 1. Score Calculate karo
foreach($user_answers as $q_id => $selected_opt) {
    // Database se sahi jawab pucho
    $sql = "SELECT correct_option FROM questions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $q_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()){
        if($row['correct_option'] === $selected_opt){
            $score++;
        }
    }
}

// 2. Score Save karo
$save_sql = "INSERT INTO quiz_history (user_id, subject, score, total_questions) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($save_sql);
$stmt->bind_param("isii", $user_id, $subject, $score, $total);
$stmt->execute();

echo json_encode([
    "status" => "success", 
    "score" => $score, 
    "total" => $total,
    "message" => "Quiz Submitted! You scored $score/$total"
]);
?>