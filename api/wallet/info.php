<?php
// api/wallet/info.php
header("Content-Type: application/json");
include '../../config/db.php';
session_start();

if(!isset($_SESSION['user_id'])){
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Get Current Balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bal_res = $stmt->get_result()->fetch_assoc();

// 2. Get Last 10 Transactions
$hist_sql = "SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$stmt2 = $conn->prepare($hist_sql);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$hist_res = $stmt2->get_result();

$history = [];
while($row = $hist_res->fetch_assoc()){
    $row['date'] = date("d M, h:i A", strtotime($row['created_at']));
    $history[] = $row;
}

echo json_encode([
    "status" => "success",
    "balance" => $bal_res['balance'],
    "history" => $history
]);
?>