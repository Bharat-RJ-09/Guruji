<?php
// api/wallet/deposit.php
header("Content-Type: application/json");
include '../../config/db.php';
session_start();

$data = json_decode(file_get_contents("php://input"));

if(!isset($_SESSION['user_id'])){
    echo json_encode(["status" => "error", "message" => "Login Required"]);
    exit;
}

$amount = (float) $data->amount;
$utr = htmlspecialchars(strip_tags($data->utr));
$user_id = $_SESSION['user_id'];

if($amount < 1 || empty($utr)) {
    echo json_encode(["status" => "error", "message" => "Invalid Amount or UTR"]);
    exit;
}

// Check if UTR already exists (Prevent Duplicate)
$check = $conn->prepare("SELECT id FROM wallet_transactions WHERE utr_number = ?");
$check->bind_param("s", $utr);
$check->execute();
if($check->get_result()->num_rows > 0){
    echo json_encode(["status" => "error", "message" => "UTR already used!"]);
    exit;
}

// Insert 'Pending' Transaction
$sql = "INSERT INTO wallet_transactions (user_id, type, amount, status, utr_number, description) VALUES (?, 'deposit', ?, 'pending', ?, 'Manual Deposit via UPI')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ids", $user_id, $amount, $utr);

if($stmt->execute()){
    echo json_encode(["status" => "success", "message" => "Deposit Request Submitted! Verify in Admin."]);
} else {
    echo json_encode(["status" => "error", "message" => "Server Error"]);
}
?>