<?php
// api/quiz/history.php

error_reporting(0);
ini_set('display_errors', 0);

// 1. Start Buffer
ob_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include '../../config/db.php';
session_start();

// 2. Clean Buffer (Fixes JSON crashes)
ob_clean();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// 3. Fetch Data
$sql = "SELECT subject, score, total_questions, time_taken, played_at 
        FROM quiz_history 
        WHERE user_id = ? 
        ORDER BY played_at DESC 
        LIMIT 50";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $history = [];
    
    while($row = $result->fetch_assoc()){
        // Format Date
        $row['date_formatted'] = date("d M Y, h:i A", strtotime($row['played_at']));
        $history[] = $row;
    }
    
    echo json_encode(["status" => "success", "data" => $history]);
} else {
    echo json_encode(["status" => "error", "message" => "Query Failed"]);
}

$conn->close();
?>