<?php
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mailer.php';

echo "=== COGNOS 2K26 EMAIL SYSTEM DIAGNOSTIC ===\n";
echo "GMAIL_WEBHOOK_URL: " . (defined('GMAIL_WEBHOOK_URL') && !empty(GMAIL_WEBHOOK_URL) ? 'CONFIGURED (' . substr(GMAIL_WEBHOOK_URL, 0, 35) . '...)' : 'NOT SET') . "\n";
echo "BREVO_API_KEY: " . (defined('BREVO_API_KEY') && !empty(BREVO_API_KEY) ? 'CONFIGURED (' . substr(BREVO_API_KEY, 0, 8) . '...)' : 'NOT SET') . "\n";
echo "RESEND_API_KEY: " . (defined('RESEND_API_KEY') && !empty(RESEND_API_KEY) ? 'CONFIGURED (' . substr(RESEND_API_KEY, 0, 8) . '...)' : 'NOT SET') . "\n";
echo "SMTP_HOST: " . SMTP_HOST . " | Port: " . SMTP_PORT . " | User: " . SMTP_USERNAME . "\n\n";

// Test 1: Network connectivity test to smtp.gmail.com on port 587 and 465
foreach ([587 => 'STARTTLS', 465 => 'SMTPS'] as $port => $name) {
    echo "Testing TCP socket to smtp.gmail.com:$port ($name)... ";
    $fp = @fsockopen(SMTP_HOST, $port, $errno, $errstr, 3);
    if ($fp) {
        echo "CONNECTED\n";
        fclose($fp);
    } else {
        echo "BLOCKED / TIMEOUT (Error $errno: $errstr)\n";
    }
}

// Test 2: Dispatch using active mailer driver
$targetEmail = $_GET['to'] ?? 'chalamalauday0@gmail.com';
echo "\n--- Attempting Confirmation Email Dispatch to $targetEmail ---\n";

$dummyStudent = [
    'reg_code' => 'DIAG-' . strtoupper(substr(md5((string)time()), 0, 5)),
    'student_name' => 'Test Participant',
    'email' => $targetEmail,
    'roll_no' => 'Y23CD999',
    'branch' => 'CSE(DS)',
    'college_name' => 'R.V.R. & J.C. College of Engineering',
    'primary_vishleshana' => 1,
    'teammate_vishleshana' => 0,
    'has_teammate' => 0
];

$res = send_registration_confirmation_email($dummyStudent, ['Vishleshana', 'Razzle Review']);
print_r($res);
