<?php
// 1. Session & DB
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

include '../../config/db.php';

// Login Check
if(!isset($_SESSION['user_id'])){
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Query: Score ka Total aur Games ki Ginti
$sql = "SELECT SUM(score) as total_score, COUNT(id) as games_played FROM quiz_history WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Agar null hai (ek bhi game nahi khela), to 0 maan lo
$total_score = $row['total_score'] ? $row['total_score'] : 0;
$games_played = $row['games_played'] ? $row['games_played'] : 0;

// 3. Rank Calculate karo (Optional - thoda advanced)
// Hum check karenge ki kitne log is user se aage hain
$rank_sql = "SELECT COUNT(*) as rank FROM (SELECT user_id, SUM(score) as s FROM quiz_history GROUP BY user_id) as scores WHERE s > ?";
$stmt2 = $conn->prepare($rank_sql);
$stmt2->bind_param("i", $total_score);
$stmt2->execute();
$rank_res = $stmt2->get_result();
$rank_row = $rank_res->fetch_assoc();
$my_rank = $rank_row['rank'] + 1; // Agar 0 log aage hain, to Rank 1 hui

echo json_encode([
    "status" => "success",
    "stats" => [
        "score" => $total_score,
        "played" => $games_played,
        "rank" => $my_rank
    ]
]);
?>