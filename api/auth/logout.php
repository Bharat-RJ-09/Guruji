<?php
session_start();

// 1. Session variables saaf karo
$_SESSION = array();

// 2. Cookie bhi uda do (agar koi hai)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Session destroy karo
session_destroy();

header("Content-Type: application/json");
echo json_encode(["status" => "success", "message" => "Logged out successfully"]);
?>