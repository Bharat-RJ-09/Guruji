<?php
// config/telegram.php

// 1. Main Bot (For Login/Signup)
define('TG_BOT_TOKEN', 'YOUR_MAIN_BOT_TOKEN'); 

// 2. 💰 Payment Bot (For Deposits)
define('PAYMENT_BOT_TOKEN', '8090587422:AAGn4xtozTAIV5JDpuH2fuv8y_8rYr42fyY'); 
define('ADMIN_CHAT_ID', 'YOUR_ADMIN_ID_HERE'); // <--- ⚠️ REPLACE THIS WITH YOUR ID

function sendTelegramMessage($chat_id, $message, $is_payment_bot = false) {
    $token = $is_payment_bot ? PAYMENT_BOT_TOKEN : TG_BOT_TOKEN;
    $url = "https://api.telegram.org/bot$token/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
?>