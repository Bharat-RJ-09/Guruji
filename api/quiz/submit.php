<?php
// api/quiz/submit.php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
header("Content-Type: application/json");
include '../../config/db.php'; 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
if (empty($input['answers'])) {
    echo json_encode(["status" => "error", "message" => "No answers submitted"]);
    exit;
}

$exam = $input['exam'] ?? 'general';
$subject = $input['subject'] ?? 'general';
$user_answers = $input['answers']; 
$time_taken = isset($input['time_taken']) ? (int)$input['time_taken'] : 0;
$score = 0;
$total = count($user_answers);

// ==========================================
// 🚀 MODE 1: JSON SCORING
// ==========================================
if ($exam === 'general') {
    $jsonFile = "../../api/data/" . strtolower($subject) . ".json";
    
    if (file_exists($jsonFile)) {
        $allData = json_decode(file_get_contents($jsonFile), true);
        
        // Create a map: ID => Correct Answer
        $correct_map = [];
        foreach ($allData as $item) {
            $correct_map[$item['id']] = strtolower(trim($item['ans']));
        }

        // Calculate Score
        foreach ($user_answers as $q_id => $user_opt) {
            if (isset($correct_map[$q_id]) && $correct_map[$q_id] === strtolower($user_opt)) {
                $score++;
            }
        }
    }
} 
// ==========================================
// 🏛️ MODE 2: DATABASE SCORING
// ==========================================
else {
    $ids = array_keys($user_answers);
    if (!empty($ids)) {
        $ids_placeholder = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        
        $stmt = $conn->prepare("SELECT id, correct_option FROM questions WHERE id IN ($ids_placeholder)");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();

        $correct_map = [];
        while ($row = $result->fetch_assoc()) {
            $correct_map[$row['id']] = strtolower(trim($row['correct_option']));
        }

        foreach ($user_answers as $q_id => $user_opt) {
            if (isset($correct_map[$q_id]) && $correct_map[$q_id] === strtolower($user_opt)) {
                $score++;
            }
        }
    }
}

// 3. Save History (Common for both)
$user_id = $_SESSION['user_id'];
$log_stmt = $conn->prepare("INSERT INTO quiz_history (user_id, subject, score, total_questions, time_taken) VALUES (?, ?, ?, ?, ?)");
$log_stmt->bind_param("isiii", $user_id, $subject, $score, $total, $time_taken);

if($log_stmt->execute()){
    echo json_encode([
        "status" => "success", 
        "score" => $score, 
        "total" => $total
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "DB Error"]);
}
?>

 