<?php
// 1. Database Connect karo
include 'db.php';
header('Content-Type: application/json');

// 2. Check karo ki data aaya hai ya nahi
if (!isset($_POST['full_name']) || !isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['password'])) {
    echo json_encode(["status" => "error", "message" => "Please fill all fields!"]);
    exit;
}

// 3. Data variables mein lo
$name = $_POST['full_name'];
$user = $_POST['username'];
$email = $_POST['email'];
$pass = $_POST['password'];

// 4. Check karo: Kya Username ya Email pehle se hai?
$check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$check->bind_param("ss", $user, $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Username or Email already exists!"]);
    exit;
}
$check->close();

// 5. Password ko Encrypt karo (Security 🔒)
$hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

// 6. Naya User Save karo
$stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $user, $email, $hashed_pass);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Account Created Successfully! Login now."]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>