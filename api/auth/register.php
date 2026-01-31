<?php
// 1. Headers (Frontend ko batane ke liye ki ye JSON data hai)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 2. Database aur Mailer connect karo
// (Path dhyan se dekhna, hum 'api/auth' folder mein hain, isliye '../../' use kiya)
include_once '../../config/db.php';
include_once '../../utils/mailer.php';

// 3. Frontend se Data lo (JSON format mein)
$data = json_decode(file_get_contents("php://input"));

// 4. Data Validation (Kya sab kuch bhara hai?)
if(
    !isset($data->full_name) || 
    !isset($data->username) || 
    !isset($data->email) || 
    !isset($data->password)
){
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Incomplete Data. Please fill all fields."]);
    exit;
}

// Data ko clean karo (HTML tags hatao)
$full_name = htmlspecialchars(strip_tags($data->full_name));
$username = htmlspecialchars(strip_tags($data->username));
$email = htmlspecialchars(strip_tags($data->email));
$password = htmlspecialchars(strip_tags($data->password));

// 5. Password Strength Check
if(strlen($password) < 6){
    echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters long."]);
    exit;
}

// 6. Duplicate Check (Kya ye User pehle se hai?)
$check_sql = "SELECT id FROM users WHERE email = ? OR username = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("ss", $email, $username);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0){
    echo json_encode(["status" => "error", "message" => "Username or Email already exists!"]);
    exit;
}
$stmt->close();

// 7. Password Hashing (Security 🔒)
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// 8. User ko Database mein daalo (Pending Verification)
$insert_sql = "INSERT INTO users (full_name, username, email, password_hash, is_verified) VALUES (?, ?, ?, ?, 0)";
$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("ssss", $full_name, $username, $email, $password_hash);

if($stmt->execute()){
    
    // 9. OTP Generate karo (6 Digit)
    $otp = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime('+15 minutes')); // 15 min valid

    // OTP ko 'password_resets' table mein save karo verification ke liye
    // (Hum reuse kar rahe hain table ko taaki nayi table na banani pade)
    $otp_sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
    $otp_stmt = $conn->prepare($otp_sql);
    $otp_stmt->bind_param("sss", $email, $otp, $expiry);
    
    if($otp_stmt->execute()){
        
        // 10. Email Bhejo (SendGrid function use karke)
        $subject = "Verify your Account - NextEdu";
        $message = "<h3>Welcome to NextEdu, $full_name!</h3><p>Your verification OTP is: <strong>$otp</strong></p><p>This OTP is valid for 15 minutes.</p>";
        
        // Note: Agar SendGrid setup nahi hai to ye fail ho sakta hai, par user create ho jayega.
        // Testing ke liye hum OTP ko response mein bhi bhej rahe hain (Isse Production mein hata dena)
        $mailSent = sendMail($email, $subject, $message);
        
        http_response_code(201); // Created
        echo json_encode([
            "status" => "success", 
            "message" => "User registered! Please check email for OTP.",
            "debug_otp" => $otp // 🧪 Testing ke liye (Baad mein hata dena)
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "User created but failed to generate OTP."]);
    }

} else {
    http_response_code(503); // Service Unavailable
    echo json_encode(["status" => "error", "message" => "Unable to register user. Try again."]);
}

$conn->close();
?>