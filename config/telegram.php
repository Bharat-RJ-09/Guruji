<?php
// config/telegram.php

// 1. Load Secrets
$secrets = include __DIR__ . '/secrets.php';

if (!defined('BOT_TOKEN')) define('BOT_TOKEN', $secrets['bot_token']);
if (!defined('BOT_USERNAME')) define('BOT_USERNAME', $secrets['bot_username']);
if (!defined('ADMIN_CHAT_ID')) define('ADMIN_CHAT_ID', $secrets['admin_chat_id']);

/**
 * Send a Message via Telegram
 */
function sendTelegramMessage($chat_id, $message) {
    if (!$chat_id) return false;
    
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
    
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
?>