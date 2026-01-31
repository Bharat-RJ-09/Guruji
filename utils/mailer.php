<?php
// Direct access block
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) { die("❌ Forbidden."); }

function sendMail($toEmail, $subject, $messageBody) {
    // SendGrid API Key (Yahan apni Key dalna baad mein)
    $apiKey = 'YOUR_SENDGRID_API_KEY_HERE';

    $url = 'https://api.sendgrid.com/v3/mail/send';

    $data = [
        "personalizations" => [
            [
                "to" => [
                    ["email" => $toEmail]
                ],
                "subject" => $subject
            ]
        ],
        "from" => [
            "email" => "no-reply@nextedu.com", // SendGrid me verify kiya hua email
            "name"  => "NextEdu Security"
        ],
        "content" => [
            [
                "type" => "text/html",
                "value" => $messageBody
            ]
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Agar 200-299 code aaya to success
    return ($httpCode >= 200 && $httpCode < 300);
}
?>