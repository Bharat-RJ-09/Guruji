<?php
// api/admin/set_pass.php
include '../../config/db.php';

// 1. The Password you want
$pass = "admin123";

// 2. Generate Real Hash
$hash = password_hash($pass, PASSWORD_DEFAULT);

// 3. Update Database
$stmt = $conn->prepare("UPDATE admin_settings SET setting_value = ? WHERE setting_key = 'admin_password'");
$stmt->bind_param("s", $hash);

if($stmt->execute()){
    echo "<h1>✅ Success!</h1>";
    echo "<p>Password set to: <b>admin123</b></p>";
    echo "<p>Hash stored: $hash</p>";
    echo "<br><a href='../../admin-login.html'>Go to Login</a>";
} else {
    echo "Error: " . $conn->error;
}
?>