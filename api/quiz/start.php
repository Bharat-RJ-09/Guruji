<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once '../../config/db.php';

// Check Login
if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Please Login First."]);
    exit;
}

// Subject lo URL se (e.g., ?subject=gk)
$subject = isset($_GET['subject']) ? $_GET['subject'] : 'gk';

// 10 Random Questions nikalo (BINA ANSWER KE)
// Note: Hum 'id' bhej rahe hain taaki baad mein check kar sakein, par 'correct_option' nahi.
$sql = "SELECT id, question_text, option_a, option_b, option_c, option_d FROM questions WHERE subject = ? ORDER BY RAND() LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $subject);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while($row = $result->fetch_assoc()){
    $questions[] = $row;
}

echo json_encode(["status" => "success", "data" => $questions]);
?>