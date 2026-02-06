<?php
// api/admin/setup_tables.php
include '../../config/db.php';

// 1. Questions Table
$sql1 = "CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam VARCHAR(50) NOT NULL,
    subject VARCHAR(50) NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option CHAR(1) NOT NULL, -- 'a', 'b', 'c', or 'd'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// 2. Quiz History Table
$sql2 = "CREATE TABLE IF NOT EXISTS quiz_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(50),
    score INT,
    total_questions INT,
    time_taken INT, -- in seconds
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id)
)";

// 3. Admin Settings (for dynamic pricing/passwords)
$sql3 = "CREATE TABLE IF NOT EXISTS admin_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE,
    setting_value VARCHAR(255)
)";

// Execute
$conn->query($sql1);
$conn->query($sql2);
$conn->query($sql3);

// Insert Default Admin Password (Hash of 'admin123')
$pass = password_hash("admin123", PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO admin_settings (setting_key, setting_value) VALUES ('admin_password', '$pass')");
$conn->query("INSERT IGNORE INTO admin_settings (setting_key, setting_value) VALUES ('standard_price', '99')");
$conn->query("INSERT IGNORE INTO admin_settings (setting_key, setting_value) VALUES ('prime_price', '199')");

echo "✅ Database Tables & Default Admin Settings Created!";
?>