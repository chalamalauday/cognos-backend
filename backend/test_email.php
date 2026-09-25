<?php
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

echo "=== COGNOS 2K26 SMTP DIAGNOSTIC ===\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Username: " . SMTP_USERNAME . "\n";
echo "From Email: " . SMTP_FROM_EMAIL . "\n\n";

// Test 1: Network connectivity test to smtp.gmail.com on port 587 and 465
foreach ([587 => 'STARTTLS', 465 => 'SMTPS'] as $port => $name) {
    echo "Testing socket connection to smtp.gmail.com on port $port ($name)... ";
    $fp = @fsockopen(SMTP_HOST, $port, $errno, $errstr, 5);
    if ($fp) {
        echo "SUCCESS! (Connected)\n";
        fclose($fp);
    } else {
        echo "FAILED! Error $errno: $errstr\n";
    }
}

echo "\n--- Attempting PHPMailer Dispatch (with full SMTP debug) ---\n";

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo "SMTP ($level): $str\n";
    };

    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = (SMTP_SECURE === 'ssl' || SMTP_PORT == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 10;
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    $testRecipient = $_GET['to'] ?? 'chalamalauday0@gmail.com';
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($testRecipient, 'Test Candidate');
    $mail->Subject = 'Test Email from COGNOS 2K26 on Render';
    $mail->Body    = 'Hello! If you are reading this, Gmail SMTP from Render is working 100% properly!';

    $mail->send();
    echo "\n>>> RESULT: SUCCESS! Test email was accepted by smtp.gmail.com and dispatched to $testRecipient! <<<\n";
} catch (\Exception $e) {
    echo "\n>>> RESULT: FAILED! <<<\n";
    echo "Error: " . $mail->ErrorInfo . "\n";
    echo "Exception: " . $e->getMessage() . "\n";
}
