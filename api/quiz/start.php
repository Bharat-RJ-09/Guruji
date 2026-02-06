<?php
// api/quiz/start.php
session_start();
header("Content-Type: application/json");
include '../../config/db.php';

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Please Login First"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$subject = isset($_GET['subject']) ? $_GET['subject'] : 'gk'; 
$exam = isset($_GET['exam']) ? $_GET['exam'] : 'general'; 

// 2. CHECK DAILY LIMITS (Same for both)
$stmt = $conn->prepare("SELECT subscription_plan FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc()['subscription_plan'] ?? 'free';

$limits = ['free' => 5, 'standard' => 30, 'prime' => 999999];
$daily_limit = $limits[$plan];

$date_today = date('Y-m-d');
$c_stmt = $conn->prepare("SELECT COUNT(*) as played FROM quiz_history WHERE user_id = ? AND DATE(played_at) = ?");
$c_stmt->bind_param("is", $user_id, $date_today);
$c_stmt->execute();
$played_today = $c_stmt->get_result()->fetch_assoc()['played'];

if ($played_today >= $daily_limit) {
    echo json_encode(["status" => "error", "message" => "Daily Limit Reached! Upgrade to play more."]);
    exit;
}

$questions = [];

// ==========================================
// 🚀 MODE 1: JSON FILES (Start Practice)
// ==========================================
if ($exam === 'general') {
    // Map subjects to filenames
    // Ensure the file exists in api/data/
    $jsonFile = "../../api/data/" . strtolower($subject) . ".json";
    
    if (file_exists($jsonFile)) {
        $jsonContent = file_get_contents($jsonFile);
        $allQuestions = json_decode($jsonContent, true);
        
        if ($allQuestions) {
            // Shuffle and pick 10
            shuffle($allQuestions);
            $selected = array_slice($allQuestions, 0, 10);
            
            // REMAP keys to match Database format (Frontend expects specific keys)
            foreach ($selected as $q) {
                $questions[] = [
                    "id" => $q['id'],
                    "question_text" => $q['q'], // JSON 'q' -> DB 'question_text'
                    "option_a" => $q['a'],
                    "option_b" => $q['b'],
                    "option_c" => $q['c'],
                    "option_d" => $q['d']
                ];
            }
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Subject file not found ($subject)"]);
        exit;
    }
} 

// ==========================================
// 🏛️ MODE 2: DATABASE (Exam Batches)
// ==========================================
else {
    $q_stmt = $conn->prepare("SELECT id, question_text, option_a, option_b, option_c, option_d FROM questions WHERE subject = ? AND exam = ? ORDER BY RAND() LIMIT 10");
    $q_stmt->bind_param("ss", $subject, $exam);
    $q_stmt->execute();
    $result = $q_stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}

// 4. Return Result
if (count($questions) < 1) {
    echo json_encode(["status" => "error", "message" => "No questions found."]);
} else {
    echo json_encode([
        "status" => "success", 
        "data" => $questions, 
        "remaining" => ($daily_limit - $played_today)
    ]);
}
?>


  
