<?php
session_start();
header("Content-Type: application/json");
include_once '../../config/db.php'; // DB connection chahiye Score save karne ke liye

if(!isset($_SESSION['user_id'])){
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user_id'];
$subject = $data['subject'];
$user_answers = $data['answers'];

// 1. JSON File Read karo
$json_file = "../data/" . $subject . ".json";
if (!file_exists($json_file)) {
    echo json_encode(["status" => "error", "message" => "Server Error: Data missing"]);
    exit;
}

$json_data = file_get_contents($json_file);
$all_questions = json_decode($json_data, true);

// 2. Questions ko ID ke hisab se map kar lo (Fast checking ke liye)
$question_map = [];
foreach ($all_questions as $q) {
    $question_map[$q['id']] = $q['ans'];
}

// 3. Score Calculate karo
$score = 0;
$total = count($user_answers);

foreach($user_answers as $q_id => $selected_opt) {
    // Check karo kya ye ID map mein hai aur answer sahi hai?
    if (isset($question_map[$q_id]) && $question_map[$q_id] === $selected_opt) {
        $score++;
    }
}

// 4. Score Database mein Save karo (History Table)
$save_sql = "INSERT INTO quiz_history (user_id, subject, score, total_questions) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($save_sql);
$stmt->bind_param("isii", $user_id, $subject, $score, $total);

if($stmt->execute()){
    echo json_encode([
        "status" => "success", 
        "score" => $score, 
        "total" => $total,
        "message" => "Quiz Submitted! You scored $score/$total"
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to save score"]);
}
?>