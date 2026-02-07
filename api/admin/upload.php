<?php
// api/admin/upload.php
session_start();
header("Content-Type: application/json");
include '../../config/db.php';

// Check Admin Login (Simple Session Check)
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["status" => "error", "message" => "Admin Access Required"]);
    exit;
}

if (!isset($_FILES['file']) || !isset($_POST['exam']) || !isset($_POST['subject'])) {
    echo json_encode(["status" => "error", "message" => "Missing File or Exam/Subject Info"]);
    exit;
}

$exam = $_POST['exam'];       // e.g., 'bstc'
$subject = $_POST['subject']; // e.g., 'gk'
$file = $_FILES['file']['tmp_name'];

if (($handle = fopen($file, "r")) !== FALSE) {
    $count = 0;
    
    // Prepare Insert Statement
    $stmt = $conn->prepare("INSERT INTO questions (exam, subject, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Skip Header Row if it exists (Optional logic)
        // Format expected: Question, A, B, C, D, Answer(a/b/c/d)
        if(count($data) < 6) continue; 
        
        $q_text = $data[0];
        $opt_a = $data[1];
        $opt_b = $data[2];
        $opt_c = $data[3];
        $opt_d = $data[4];
        $correct = strtolower(trim($data[5])); // Ensure 'A' becomes 'a'

        $stmt->bind_param("ssssssss", $exam, $subject, $q_text, $opt_a, $opt_b, $opt_c, $opt_d, $correct);
        if($stmt->execute()) $count++;
    }
    
    fclose($handle);
    echo json_encode(["status" => "success", "message" => "Uploaded $count questions successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Cannot read file"]);
}
?>