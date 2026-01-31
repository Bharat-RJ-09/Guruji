<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once '../../config/db.php';

// Subject filter (Default: All Mix)
$subject = isset($_GET['subject']) ? $_GET['subject'] : 'all';

if ($subject == 'all') {
    // Sab subjects ka total score (Advanced Query)
    $sql = "SELECT u.full_name, u.username, SUM(q.score) as total_score, COUNT(q.id) as quizzes_played 
            FROM quiz_history q 
            JOIN users u ON q.user_id = u.id 
            GROUP BY q.user_id 
            ORDER BY total_score DESC LIMIT 50";
    $stmt = $conn->prepare($sql);
} else {
    // Specific Subject Score (Highest score first)
    $sql = "SELECT u.full_name, u.username, q.score as total_score, q.played_at 
            FROM quiz_history q 
            JOIN users u ON q.user_id = u.id 
            WHERE q.subject = ? 
            ORDER BY q.score DESC LIMIT 50";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $subject);
}

$stmt->execute();
$result = $stmt->get_result();

$rankers = [];
while($row = $result->fetch_assoc()){
    $rankers[] = $row;
}

echo json_encode(["status" => "success", "data" => $rankers]);
?>