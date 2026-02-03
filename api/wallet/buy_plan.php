<?php
// api/wallet/buy_plan.php
header("Content-Type: application/json");
include '../../config/db.php';
session_start();

// 1. Defined Plans & Prices (Server-Side Security) 
// FETCH PRICES FROM DB
$p_res = $conn->query("SELECT setting_key, setting_value FROM admin_settings");
$prices = [];
while($row = $p_res->fetch_assoc()) $prices[$row['setting_key']] = $row['setting_value'];

$PLANS = [
    "standard" => ["price" => $prices['standard_price'], "name" => "Standard Plan"],
    "prime"    => ["price" => $prices['prime_price'], "name" => "Prime Batch"]
]; 

$data = json_decode(file_get_contents("php://input"));
$user_id = $_SESSION['user_id'];
$plan_type = isset($data->plan) ? $data->plan : '';

// 2. Validate Plan
if (!array_key_exists($plan_type, $PLANS)) {
    echo json_encode(["status" => "error", "message" => "Invalid Plan Selected"]);
    exit;
}

$plan_price = $PLANS[$plan_type]['price'];
$plan_name = $PLANS[$plan_type]['name'];

// 3. Check Wallet Balance
$stmt = $conn->prepare("SELECT balance, subscription_plan FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user['subscription_plan'] === $plan_type) {
    echo json_encode(["status" => "error", "message" => "You already have this plan!"]);
    exit;
}

if ($user['balance'] < $plan_price) {
    echo json_encode(["status" => "error", "message" => "Insufficient Balance. Please Add Money."]);
    exit;
}

// 4. Process Transaction
$conn->begin_transaction();

try {
    // Deduct Balance & Update Plan
    $new_bal = $user['balance'] - $plan_price;
    $update_user = $conn->prepare("UPDATE users SET balance = ?, subscription_plan = ? WHERE id = ?");
    $update_user->bind_param("dsi", $new_bal, $plan_type, $user_id);
    $update_user->execute();

    // Log Transaction
    $log = $conn->prepare("INSERT INTO wallet_transactions (user_id, type, amount, status, description) VALUES (?, 'purchase', ?, 'success', ?)");
    $desc = "Upgraded to " . $plan_name;
    $log->bind_param("ids", $user_id, $plan_price, $desc);
    $log->execute();

    $conn->commit();
    echo json_encode(["status" => "success", "message" => "🎉 Upgraded to " . $plan_name . "!"]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Transaction Failed"]);
}


?>