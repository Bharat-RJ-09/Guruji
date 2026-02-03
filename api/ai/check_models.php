<?php
// api/ai/check_models.php
header("Content-Type: application/json");
error_reporting(0);

// 👇 PASTE YOUR KEY HERE 👇
$apiKey = "AIzaSyCgbVNWVMQCGBuR7Y1XjmnTzdMleJ8ho5A"; 

// 1. Ask Google for the list of available models
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . trim($apiKey);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

$response = curl_exec($ch);
$result = json_decode($response, true);

// 2. Filter & Display the Valid Ones
$valid_models = [];

if(isset($result['models'])){
    foreach($result['models'] as $model){
        // We only want models that can "generateContent" (Chat models)
        if(in_array("generateContent", $model['supportedGenerationMethods'])){
            $valid_models[] = $model['name'];
        }
    }
    echo json_encode([
        "status" => "success", 
        "message" => "Found " . count($valid_models) . " working models.",
        "valid_models" => $valid_models
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Could not fetch models. Check Key.", 
        "google_response" => $result
    ], JSON_PRETTY_PRINT);
}
?>