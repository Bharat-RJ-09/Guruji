<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Login Check
if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Please Login First."]);
    exit;
}

$subject = isset($_GET['subject']) ? $_GET['subject'] : 'english';

// 1. JSON File ka path dhundo
// Filhal sirf english ke liye set kar rahe hain, baaki subjects ke liye alag files bana lena
$json_file = "../data/" . $subject . ".json";

if (!file_exists($json_file)) {
    echo json_encode(["status" => "error", "message" => "Question file not found for $subject"]);
    exit;
}

// 2. File Read karo
$json_data = file_get_contents($json_file);
$all_questions = json_decode($json_data, true);

// 3. Shuffle karo (Taaki har baar naye sawal aayein)
shuffle($all_questions);

// 4. Pehle 10 sawal nikalo
$selected_questions = array_slice($all_questions, 0, 10);

// 5. IMPORTANT: Answer key hata do (Security 🔒)
$frontend_questions = [];
foreach ($selected_questions as $q) {
    $frontend_questions[] = [
        "id" => $q['id'],
        "question_text" => $q['q'], // Frontend wale naam se map kiya
        "option_a" => $q['a'],
        "option_b" => $q['b'],
        "option_c" => $q['c'],
        "option_d" => $q['d']
    ];
}

echo json_encode(["status" => "success", "data" => $frontend_questions]);
?>