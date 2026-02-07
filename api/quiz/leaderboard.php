<?php
// api/quiz/leaderboard.php
header("Content-Type: application/json");
include '../../config/db.php';

$subject = isset($_GET['subject']) ? $_GET['subject'] : 'all';

// SQL Logic:
// We need to JOIN users table to get names.
// We group by user_id to sum their scores.

if ($subject === 'all') {
    // Overall Leaderboard (Sum of ALL subjects)
    $sql = "SELECT 
                u.full_name, 
                u.username, 
                SUM(h.score) as total_score, 
                SUM(h.time_taken) as total_time
            FROM quiz_history h
            JOIN users u ON h.user_id = u.id
            GROUP BY h.user_id
            ORDER BY total_score DESC, total_time ASC
            LIMIT 50";
    $stmt = $conn->prepare($sql);
} else {
    // Subject Specific Leaderboard
    $sql = "SELECT 
                u.full_name, 
                u.username, 
                SUM(h.score) as total_score, 
                SUM(h.time_taken) as total_time
            FROM quiz_history h
            JOIN users u ON h.user_id = u.id
            WHERE h.subject = ?
            GROUP BY h.user_id
            ORDER BY total_score DESC, total_time ASC
            LIMIT 50";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $subject);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(["status" => "success", "data" => $data]);
?>