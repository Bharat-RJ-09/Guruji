<?php
// api/wallet/manual_deposit.php
header("Content-Type: application/json");
include '../../config/db.php';
include '../../config/telegram.php';
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

// 2. Input Sanitization
$amount = isset($data['amount']) ? (float)$data['amount'] : 0;
$utr = isset($data['utr']) ? trim($data['utr']) : '';

if ($amount < 1) {
    echo json_encode(["status" => "error", "message" => "Invalid Amount. Minimum ₹1."]);
    exit;
}
if (strlen($utr) < 8 || strlen($utr) > 24) {
    echo json_encode(["status" => "error", "message" => "Invalid UTR/Reference Number."]);
    exit;
}

// 3. Anti-Duplicate Check
$check = $conn->prepare("SELECT id FROM wallet_transactions WHERE description LIKE ?");
$search = "%$utr%";
$check->bind_param("s", $search);
$check->execute();
if($check->get_result()->num_rows > 0){
    echo json_encode(["status" => "error", "message" => "This UTR has already been submitted!"]);
    exit;
}

// 4. Fetch User Info
$u_stmt = $conn->prepare("SELECT full_name, username FROM users WHERE id = ?");
$u_stmt->bind_param("i", $user_id);
$u_stmt->execute();
$user = $u_stmt->get_result()->fetch_assoc();

// 5. Record Transaction (Status: Pending)
$desc = "Manual Deposit (UTR: $utr)";
$stmt = $conn->prepare("INSERT INTO wallet_transactions (user_id, type, amount, status, description, created_at) VALUES (?, 'deposit', ?, 'pending', ?, NOW())");
$stmt->bind_param("ids", $user_id, $amount, $desc);

if ($stmt->execute()) {
    
    // 6. 🚀 SEND ALERT TO OWNER ONLY
    $msg = "🚨 <b>New Payment Received!</b>\n\n" .
           "👤 <b>User:</b> {$user['full_name']} (@{$user['username']})\n" .
           "💰 <b>Amount:</b> ₹$amount\n" .
           "🧾 <b>UTR:</b> <code>$utr</code>\n" .
           "📅 <b>Time:</b> " . date("d M, h:i A") . "\n\n" .
           "<i>Please verify in bank and approve in Admin Panel.</i>";

    // 'true' tells it to use the Payment Bot
    // ADMIN_CHAT_ID ensures ONLY YOU get this
    sendTelegramMessage(ADMIN_CHAT_ID, $msg, true); 

    echo json_encode(["status" => "success", "message" => "Payment Submitted! Verification takes 10-30 mins."]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error"]);
}
?>