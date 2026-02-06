<?php
// config/telegram.php

// 1. Load Secrets securely
$secrets = include __DIR__ . '/secrets.php';

// Main Bot (For Student Login/OTP)
if (!defined('BOT_TOKEN')) define('BOT_TOKEN', $secrets['bot_token']);

// 💰 Payment Bot (For Admin Alerts ONLY)
define('PAYMENT_BOT_TOKEN', '8090587422:AAGn4xtozTAIV5JDpuH2fuv8y_8rYr42fyY'); 

// 🚨 CRITICAL: Put YOUR personal Telegram ID here. 
// Only THIS ID will receive deposit alerts.
define('ADMIN_CHAT_ID', '5412181635'); // <--- ⚠️ REPLACE THIS WITH YOUR ID 

function sendTelegramMessage($chat_id, $message, $use_payment_bot = false) {
    if (!$chat_id) return;
    
    $token = $use_payment_bot ? PAYMENT_BOT_TOKEN : BOT_TOKEN;
    $url = "https://api.telegram.org/bot$token/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
?>