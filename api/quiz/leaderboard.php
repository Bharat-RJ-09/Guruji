<?php
// api/quiz/leaderboard.php
error_reporting(0);
ini_set('display_errors', 0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include '../../config/db.php';

$subject = isset($_GET['subject']) ? $_GET['subject'] : 'all';

if ($subject == 'all') {
    // 🌍 Overall Leaderboard: Sum of BEST scores from each subject
    // Logic: First find max score per subject per user, then sum those max scores.
    $sql = "
        SELECT u.full_name, u.username, 
               SUM(best_stats.max_score) as total_score, 
               SUM(best_stats.best_time) as total_time
        FROM users u
        JOIN (
            SELECT user_id, subject, MAX(score) as max_score, MIN(time_taken) as best_time
            FROM quiz_history
            GROUP BY user_id, subject
        ) best_stats ON u.id = best_stats.user_id
        GROUP BY u.id
        ORDER BY total_score DESC, total_time ASC
        LIMIT 50
    ";
    $stmt = $conn->prepare($sql);

} else {
    // 📘 Subject Specific: Show User's BEST attempt for this subject
    $sql = "
        SELECT u.full_name, u.username, 
               MAX(q.score) as total_score, 
               MIN(q.time_taken) as total_time -- Shows time of the best score (approx logic)
        FROM quiz_history q 
        JOIN users u ON q.user_id = u.id 
        WHERE q.subject = ? 
        GROUP BY q.user_id 
        ORDER BY total_score DESC, total_time ASC 
        LIMIT 50
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $subject);
}

if ($stmt && $stmt->execute()) {
    $result = $stmt->get_result();
    $rankers = [];
    while($row = $result->fetch_assoc()){
        $rankers[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $rankers]);
} else {
    echo json_encode(["status" => "error", "message" => "Query Failed"]);
}
$conn->close();
?>