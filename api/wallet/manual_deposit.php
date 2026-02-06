<?php
// api/wallet/manual_deposit.php
header("Content-Type: application/json");
include '../../config/db.php';
include '../../config/telegram.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

$amount = $data['amount'] ?? 0;
$utr = trim($data['utr'] ?? '');

// 1. Validations
if ($amount < 1) {
    echo json_encode(["status" => "error", "message" => "Invalid Amount"]);
    exit;
}
if (strlen($utr) < 8 || strlen($utr) > 20) {
    echo json_encode(["status" => "error", "message" => "Invalid UTR / Transaction ID"]);
    exit;
}

// Check if UTR already exists (Prevent duplicate usage)
$check = $conn->prepare("SELECT id FROM wallet_transactions WHERE description LIKE ?");
$utr_search = "%$utr%";
$check->bind_param("s", $utr_search);
$check->execute();
if($check->get_result()->num_rows > 0){
    echo json_encode(["status" => "error", "message" => "This UTR is already submitted!"]);
    exit;
}

// 2. Fetch User Details for Notification
$u_stmt = $conn->prepare("SELECT full_name, username, mobile FROM users WHERE id = ?");
$u_stmt->bind_param("i", $user_id);
$u_stmt->execute();
$user = $u_stmt->get_result()->fetch_assoc();

// 3. Insert 'Pending' Transaction
$desc = "Manual Deposit (UTR: $utr)";
$stmt = $conn->prepare("INSERT INTO wallet_transactions (user_id, type, amount, status, description, created_at) VALUES (?, 'deposit', ?, 'pending', ?, NOW())");
$stmt->bind_param("ids", $user_id, $amount, $desc);

if ($stmt->execute()) {
    
    // 4. 🚀 SEND TELEGRAM ALERT TO ADMIN
    $msg = "🚨 <b>New Manual Deposit Request</b>\n\n" .
           "👤 <b>User:</b> {$user['full_name']} (@{$user['username']})\n" .
           "💰 <b>Amount:</b> ₹$amount\n" .
           "🧾 <b>UTR:</b> <code>$utr</code>\n" .
           "📅 <b>Time:</b> " . date("d M, h:i A") . "\n\n" .
           "<i>Please verify in bank and approve in Admin Panel.</i>";

    // Send to YOU (Admin) using the Payment Bot
    sendTelegramMessage(ADMIN_CHAT_ID, $msg, true); 

    echo json_encode(["status" => "success", "message" => "Request Submitted! Verification takes 10-30 mins."]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error"]);
}
?>