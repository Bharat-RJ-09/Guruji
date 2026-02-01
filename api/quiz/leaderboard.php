<?php
// api/quiz/leaderboard.php

// 1. Silent HTML Errors (Crucial for JSON)
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 2. Connect DB
if (!file_exists('../../config/db.php')) {
    echo json_encode(["status" => "error", "message" => "DB Config Missing"]);
    exit;
}
include '../../config/db.php';

// 3. Get Subject Filter
$subject = isset($_GET['subject']) ? $_GET['subject'] : 'all';

// 4. Prepare Query (Group by User to show Total Score)
if ($subject == 'all') {
    // Overall Leaderboard (Sum of ALL subjects)
    $sql = "SELECT u.full_name, u.username, 
            SUM(q.score) as total_score, 
            COUNT(q.id) as quizzes_played 
            FROM quiz_history q 
            JOIN users u ON q.user_id = u.id 
            GROUP BY q.user_id 
            ORDER BY total_score DESC LIMIT 50";
    $stmt = $conn->prepare($sql);
} else {
    // Subject Specific Leaderboard (Sum of THAT subject)
    $sql = "SELECT u.full_name, u.username, 
            SUM(q.score) as total_score, 
            COUNT(q.id) as quizzes_played 
            FROM quiz_history q 
            JOIN users u ON q.user_id = u.id 
            WHERE q.subject = ? 
            GROUP BY q.user_id 
            ORDER BY total_score DESC LIMIT 50";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $subject);
}

// 5. Execute & Return
if ($stmt && $stmt->execute()) {
    $result = $stmt->get_result();
    $rankers = [];
    while($row = $result->fetch_assoc()){
        $rankers[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $rankers]);
} else {
    echo json_encode(["status" => "error", "message" => "Query Failed: " . $conn->error]);
}

$conn->close();
?>