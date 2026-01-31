<?php
// Direct access rokne ke liye check (Security Layer 1)
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die("❌ Direct access forbidden.");
}

// Database Credentials
// NOTE: Future mein hum isse .env file se load karenge
$host = "sql300.infinityfree.com"; 
$user = "if0_38529899";
$pass = "Xe4JJvRKGhz"; // ⚠️ Bhai, GitHub pe push karne se pehle ise hata dena ya .gitignore use karna
$dbname = "if0_38529899_epiz_12345_nextedu";

// Secure Connection with Error Handling
try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    // Character Set Set karo (Emoji support ke liye)
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    // Agar error aaye to user ko sirf ye dikhao, asli error log file mein jayega
    error_log($e->getMessage()); 
    die("⚠️ Database Connection Error. Please try again later.");
}
?>