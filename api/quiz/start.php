<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// ... (Login check waisa hi rahega) ...

$subject = isset($_GET['subject']) ? $_GET['subject'] : 'english';

// 👇 PURANI LINE HATA DO:
// $json_file = "../data/" . $subject . ".json";

// 👇 YEH NAYI LINE DALO (Magic Fix 🪄):
// dirname(__DIR__) ka matlab hai 'quiz' folder se ek step peeche ('api' folder me)
$json_file = dirname(__DIR__) . "/data/" . $subject . ".json";

// Debugging ke liye (Agar ab bhi error aaye to ye uncomment karke dekhna)
// echo json_encode(["debug_path" => $json_file]); exit; 

if (!file_exists($json_file)) {
    echo json_encode(["status" => "error", "message" => "Question file not found for $subject"]);
    exit;
}

// ... (Baaki code same rahega) ...
$json_data = file_get_contents($json_file);
$all_questions = json_decode($json_data, true);

// ... (Shuffle aur baaki logic same) ...
?>