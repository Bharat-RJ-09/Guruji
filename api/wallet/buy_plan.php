<?php
// api/wallet/buy_plan.php
header("Content-Type: application/json");
include '../../config/db.php';
include '../../config/telegram.php'; // ✅ Added Telegram Support
session_start();

// 1. Settings (Fetch dynamic prices)
$p_res = $conn->query("SELECT setting_key, setting_value FROM admin_settings");
$prices = [];
while($row = $p_res->fetch_assoc()) $prices[$row['setting_key']] = $row['setting_value'];

$PLANS = [
    "standard" => ["price" => $prices['standard_price'] ?? 99, "name" => "Standard Batch", "emoji" => "⚡"],
    "prime"    => ["price" => $prices['prime_price'] ?? 199,    "name" => "Prime Batch",    "emoji" => "👑"]
]; 

$data = json_decode(file_get_contents("php://input"));
$user_id = $_SESSION['user_id'];
$plan_type = isset($data->plan) ? $data->plan : '';

if (!array_key_exists($plan_type, $PLANS)) {
    echo json_encode(["status" => "error", "message" => "Invalid Plan"]);
    exit;
}

$plan_price = $PLANS[$plan_type]['price'];
$plan_name = $PLANS[$plan_type]['name'];
$emoji = $PLANS[$plan_type]['emoji'];

// 2. Fetch User & Balance
$stmt = $conn->prepare("SELECT full_name, username, balance, subscription_plan, telegram_chat_id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user['subscription_plan'] === $plan_type) {
    echo json_encode(["status" => "error", "message" => "You are already a $plan_name member!"]);
    exit;
}

// Logic: Allow upgrade from Standard to Prime by paying difference? 
// For now, let's keep it simple: Full Price.
if ($user['balance'] < $plan_price) {
    echo json_encode(["status" => "error", "message" => "Low Balance! Please deposit money first."]);
    exit;
}

// 3. Process Upgrade
$conn->begin_transaction();

try {
    // Deduct Balance
    $new_bal = $user['balance'] - $plan_price;
    $update = $conn->prepare("UPDATE users SET balance = ?, subscription_plan = ? WHERE id = ?");
    $update->bind_param("dsi", $new_bal, $plan_type, $user_id);
    $update->execute();

    // Log Transaction
    $log = $conn->prepare("INSERT INTO wallet_transactions (user_id, type, amount, status, description) VALUES (?, 'purchase', ?, 'success', ?)");
    $desc = "Upgraded to $plan_name";
    $log->bind_param("ids", $user_id, $plan_price, $desc);
    $log->execute();

    $conn->commit();

    // 4. 🎉 TELEGRAM CELEBRATION
    if($user['telegram_chat_id']) {
        $msg = "$emoji <b>Upgrade Successful!</b>\n\n" .
               "Congratulations <b>{$user['full_name']}</b>! 🎉\n" .
               "You are now a <b>$plan_name</b> member.\n\n" .
               "<b>New Features Unlocked:</b>\n" .
               "✅ Unlimited AI Doubts\n" .
               "✅ Exclusive Themes\n" .
               "✅ Pro Leaderboard Badge\n\n" .
               "<i>Enjoy learning like a King!</i> 🤴";
        
        sendTelegramMessage($user['telegram_chat_id'], $msg);
    }

    echo json_encode([
        "status" => "success", 
        "message" => "Welcome to $plan_name!",
        "new_plan" => $plan_type
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Transaction Failed"]);
}
?>