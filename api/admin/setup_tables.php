<?php
// api/admin/setup_tables.php
include '../../config/db.php';

// 1. Users Table (CRITICAL FIX)
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    telegram_chat_id VARCHAR(50) UNIQUE, -- Required for OTP/Alerts
    otp VARCHAR(6),                      -- Stores temporary OTP
    subscription_plan ENUM('free', 'standard', 'prime') DEFAULT 'free',
    role ENUM('student', 'admin', 'super_admin') DEFAULT 'student',
    is_verified TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// 2. Questions Table
$sql_questions = "CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam VARCHAR(50) NOT NULL,
    subject VARCHAR(50) NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option CHAR(1) NOT NULL, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// 3. Quiz History Table
$sql_history = "CREATE TABLE IF NOT EXISTS quiz_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(50),
    score INT,
    total_questions INT,
    time_taken INT,
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id)
)";

// 4. Admin Settings
$sql_settings = "CREATE TABLE IF NOT EXISTS admin_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE,
    setting_value VARCHAR(255)
)";

// Execute All
$conn->query($sql_users);
$conn->query($sql_questions);
$conn->query($sql_history);
$conn->query($sql_settings);

// Insert Default Admin (Password: admin123)
$admin_pass = password_hash("admin123", PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO users (full_name, username, email, password_hash, role) VALUES ('Admin', 'admin', 'admin@nextedu.com', '$admin_pass', 'super_admin')");

echo "✅ All Tables Created Successfully (Users, Questions, History, Settings).";
?>