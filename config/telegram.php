<?php
// config/telegram.php

// 1. Load Secrets securely
$secrets = include __DIR__ . '/secrets.php';

if (!defined('BOT_TOKEN')) define('BOT_TOKEN', $secrets['bot_token']);
if (!defined('BOT_USERNAME')) define('BOT_USERNAME', $secrets['bot_username']);

// 2. Function to Send Message
function sendTelegramMessage($chat_id, $message) {
    if (!$chat_id) return;
    
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
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
    
    // Suppress output, just execute
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
?>