<?php
// 1. Database Credentials (InfinityFree wale)
$servername = "sql300.infinityfree.com"; 
$username = "if0_38529899";
$password = "Xe4JJvRKGhz";
$dbname = "if0_38529899_nextedu_db"; // ⚠️ Check karlena CPanel me yahi naam hai na?

// 2. Connection Banao
$conn = new mysqli($servername, $username, $password, $dbname);

// 3. Check Connection
if ($conn->connect_error) {
    // Agar fail ho jaye to error dikhao aur rook jao
    die("Connection failed: " . $conn->connect_error);
}

// Agar connection successful hai to kuch mat bolo (chupchap kaam karo),
// taaki ye file doosri files me include ho sake bina error diye.
?>