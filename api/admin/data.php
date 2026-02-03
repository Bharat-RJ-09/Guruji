<?php
header("Content-Type: application/json");
include '../../config/db.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    exit(json_encode(["status" => "error", "message" => "Unauthorized"]));
}

// 1. Stats
$stats = [];
$stats['total_users'] = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$stats['prime_users'] = $conn->query("SELECT COUNT(*) FROM users WHERE subscription_plan = 'prime'")->fetch_row()[0];
$stats['revenue'] = $conn->query("SELECT SUM(amount) FROM wallet_transactions WHERE status = 'success' AND type = 'purchase'")->fetch_row()[0] ?? 0;

// 2. Users List (Latest 50)
$users = [];
$res = $conn->query("SELECT id, full_name, email, subscription_plan, balance, created_at FROM users ORDER BY id DESC LIMIT 50");
while ($row = $res->fetch_assoc()) $users[] = $row;

// 3. Prices
$prices = [];
$prices['standard'] = $conn->query("SELECT setting_value FROM admin_settings WHERE setting_key = 'standard_price'")->fetch_assoc()['setting_value'];
$prices['prime'] = $conn->query("SELECT setting_value FROM admin_settings WHERE setting_key = 'prime_price'")->fetch_assoc()['setting_value'];

echo json_encode(["status" => "success", "stats" => $stats, "users" => $users, "prices" => $prices]);
?>