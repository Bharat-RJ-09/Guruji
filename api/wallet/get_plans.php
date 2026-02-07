<?php
// api/wallet/get_plans.php
header("Content-Type: application/json");
include '../../config/db.php';

// Fetch dynamic prices from Admin Settings
$sql = "SELECT setting_key, setting_value FROM admin_settings WHERE setting_key IN ('standard_price', 'prime_price')";
$result = $conn->query($sql);

$prices = [
    'standard' => 99, // Default fallback
    'prime' => 199
];

while($row = $result->fetch_assoc()) {
    if($row['setting_key'] == 'standard_price') $prices['standard'] = (int)$row['setting_value'];
    if($row['setting_key'] == 'prime_price') $prices['prime'] = (int)$row['setting_value'];
}

echo json_encode([
    "status" => "success",
    "plans" => $prices
]);
?>  