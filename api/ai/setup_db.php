<?php
// api/ai/setup_db.php
// ⚠️ RUN THIS FILE ONCE TO CREATE THE LOG TABLE

include '../../config/db.php';

$sql = "CREATE TABLE IF NOT EXISTS ai_usage_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Success: 'ai_usage_logs' table created successfully!";
} else {
    echo "❌ Error: " . $conn->error;
}
?>