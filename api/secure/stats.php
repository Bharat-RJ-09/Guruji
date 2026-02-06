<?php
// api/secure/stats.php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");
include '../../config/db.php';

if(!isset($_SESSION['user_id'])){
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Get Score Data
$sql = "SELECT SUM(score) as total_score, COUNT(id) as games_played FROM quiz_history WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

$score = $row['total_score'] ? (int)$row['total_score'] : 0;
$games = $row['games_played'] ? (int)$row['games_played'] : 0;

// 2. 🎮 Calculate Level & XP
// Logic: Every 200 points = 1 Level Up
$xp_per_level = 200;
$current_level = floor($score / $xp_per_level) + 1;
$current_xp = $score % $xp_per_level;
$progress_percent = ($current_xp / $xp_per_level) * 100;

// 3. Get Rank
$rank_sql = "SELECT COUNT(*) as rank FROM (SELECT user_id, SUM(score) as s FROM quiz_history GROUP BY user_id) as scores WHERE s > ?";
$stmt2 = $conn->prepare($rank_sql);
$stmt2->bind_param("i", $score);
$stmt2->execute();
$my_rank = $stmt2->get_result()->fetch_assoc()['rank'] + 1;

echo json_encode([
    "status" => "success",
    "stats" => [
        "score" => $score,
        "played" => $games,
        "rank" => $my_rank,
        "level" => $current_level,
        "xp" => $current_xp,
        "next_level_xp" => $xp_per_level,
        "progress" => $progress_percent
    ]
]);
?>