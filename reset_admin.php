<?php
// reset_admin.php
include 'config/db.php'; // Ensure this points to your config file

$new_password = "YourNewPasswordHere"; // <--- CHANGE THIS

// 1. Hash the password
$hash = password_hash($new_password, PASSWORD_BCRYPT);

// 2. Update Database
$stmt = $conn->prepare("UPDATE admin_settings SET setting_value = ? WHERE setting_key = 'admin_password'");
$stmt->bind_param("s", $hash);

if ($stmt->execute()) {
    echo "✅ Success! New password is: <b>$new_password</b><br>";
    echo "Please delete this file immediately.";
} else {
    echo "❌ Error: " . $conn->error;
}
?>