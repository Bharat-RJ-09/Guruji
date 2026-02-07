<?php
// api/admin/setup_avatar.php
include '../../config/db.php';

// 1. Add 'avatar' column
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(50) DEFAULT 'av1'";

if ($conn->query($sql) === TRUE) {
    echo "✅ Database Updated: Avatar column added!";
} else {
    echo "❌ Error: " . $conn->error;
}
?>