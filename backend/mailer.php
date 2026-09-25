<?php
/**
 * COGNOS 2K26 - Multi-Driver Email Notification System
 * Supports:
 *   1. Google Apps Script Web App (Gmail over HTTPS Port 443 - Recommended for Render)
 *   2. Brevo (Sendinblue) REST API (HTTPS Port 443)
 *   3. Resend REST API (HTTPS Port 443)
 *   4. PHPMailer Direct SMTP (Local XAMPP / Open Port Environments)
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

function send_registration_confirmation_email($studentData, $selectedEvents) {
    $toEmail = trim($studentData['email'] ?? '');
    $toName  = trim($studentData['student_name'] ?? 'Participant');
    $regCode = trim($studentData['reg_code'] ?? '');
    $teammateEmail = (!empty($studentData['has_teammate']) && !empty($studentData['teammate_email'])) ? trim($studentData['teammate_email']) : '';

    if (empty($toEmail)) {
        return ['sent' => false, 'message' => 'Recipient email is missing.'];
    }

    $subject = 'Registration Confirmation – ' . FEST_NAME . ' | ID: ' . $regCode;

    // 1. Build Dynamic Registered Challenges Section
    $challengesText = '';
    foreach ($selectedEvents as $eventName) {
        $eNameLower = strtolower($eventName);

        if (strpos($eNameLower, 'vishleshana') !== false) {
            $challengesText .= "Vishleshana (Group Discussion Rounds)\n" .
                "• Time: 2:00 PM – 5:00 PM\n" .
                "• Venue: Panel 1: CM SCE Lab | Panel 2: Dassault Systemes Lab (Decennial Block, 3rd Floor)\n" .
                "• Prize Pool: ₹6,000 (1st: ₹3,000 | 2nd: ₹2,000 | 3rd: ₹1,000)\n" .
                "• Faculty Contacts: Dr. Ganji Ramanjaiah (9848332853) | Dr. Riaz Shaik (9966743943)\n" .
                "• Student Contacts: Bonamukkala Ajay (7207039202) | Shaik Ayesha Rizwana\n\n";
        } elseif (strpos($eNameLower, 'razzle') !== false) {
            $challengesText .= "Razzle Review (Paper Presentation)\n" .
                "• Time: 11:00 AM Onwards\n" .
                "• Venue: CM SCE Lab (Decennial Block, 3rd Floor)\n" .
                "• Prize Pool: ₹6,000 (1st: ₹3,000 | 2nd: ₹2,000 | 3rd: ₹1,000)\n" .
                "• Faculty Contacts: Dr. Ch. Sudha Sree (9494641234) | Dr. Vallabhajosyula Sasikala (7976835016)\n" .
                "• Student Contacts: Ponnaluri Jeyanth (8331927193) | N Gayatri\n\n";
        } elseif (strpos($eNameLower, 'data') !== false || strpos($eNameLower, 'dazzle') !== false) {
            $challengesText .= "Data Dazzle (Data Storytelling & Dashboard Presentation)\n" .
                "• Time: 1:00 PM – 3:00 PM\n" .
                "• Venue: Dassault Systemes Lab (Decennial Block, 3rd Floor)\n" .
                "• Prize Pool: ₹6,000 (1st: ₹3,000 | 2nd: ₹2,000 | 3rd: ₹1,000)\n" .
                "• Faculty Contacts: Dr. R. V. Kishore Kumar (9885993494) | Mr. Rallabandi Ch S N P Sairam (8328505878)\n" .
                "• Student Contacts: Jarabana Krishna Kanth (7013162268) | Sahitya B.\n\n";
        }
    }

    // Teammate section
    $teammateText = '';
    if (!empty($studentData['has_teammate']) && !empty($studentData['teammate_name'])) {
        $teammateText = "--- TEAMMATE DETAILS ---\n" .
            "• Name: " . $studentData['teammate_name'] . "\n" .
            "• Email: " . $studentData['teammate_email'] . "\n" .
            "• Roll Number: " . $studentData['teammate_roll_no'] . "\n" .
            "• Branch: " . $studentData['teammate_branch'] . "\n" .
            "• College: " . $studentData['teammate_college'] . "\n\n";
    }

    $studentCaps = strtoupper($studentData['student_name']);
    $waLink = defined('WHATSAPP_COMMUNITY_LINK') ? WHATSAPP_COMMUNITY_LINK : 'https://chat.whatsapp.com/invite/cognos2k26';

    $safeName = htmlspecialchars($studentData['student_name'], ENT_QUOTES, 'UTF-8');
    $safeCollege = htmlspecialchars($studentData['college_name'], ENT_QUOTES, 'UTF-8');
    $safeRollNo = htmlspecialchars($studentData['roll_no'], ENT_QUOTES, 'UTF-8');
    $safeRegCode = htmlspecialchars($regCode, ENT_QUOTES, 'UTF-8');
    $safeWaLink = htmlspecialchars($waLink, ENT_QUOTES, 'UTF-8');

    $challengesHtml = '';
    foreach (explode("\n\n", trim($challengesText)) as $challenge) {
        if (trim($challenge) === '') continue;
        $challengeLines = explode("\n", $challenge);
        $challengeTitle = htmlspecialchars(array_shift($challengeLines), ENT_QUOTES, 'UTF-8');
        $challengeDetails = nl2br(htmlspecialchars(implode("\n", $challengeLines), ENT_QUOTES, 'UTF-8'));
        $challengesHtml .= '<div style="margin:0 0 14px;padding:16px;background:#fff7ed;border-left:4px solid #f97316;border-radius:8px;">' .
            '<strong style="display:block;color:#c2410c;font-size:16px;margin-bottom:6px;">' . $challengeTitle . '</strong>' .
            '<span style="color:#475569;font-size:13px;line-height:1.7;">' . $challengeDetails . '</span></div>';
    }

    $teammateHtml = '';
    if (!empty($studentData['has_teammate']) && !empty($studentData['teammate_name'])) {
        $teammateHtml = '<div style="margin:18px 0;padding:16px;background:#ecfeff;border-left:4px solid #06b6d4;border-radius:8px;">' .
            '<strong style="display:block;color:#0e7490;margin-bottom:6px;">Team-mate details</strong>' .
            '<span style="color:#334155;line-height:1.7;">' .
            htmlspecialchars($studentData['teammate_name'], ENT_QUOTES, 'UTF-8') . '<br>' .
            htmlspecialchars($studentData['teammate_email'], ENT_QUOTES, 'UTF-8') . '<br>' .
            htmlspecialchars($studentData['teammate_roll_no'], ENT_QUOTES, 'UTF-8') . ' | ' .
            htmlspecialchars($studentData['teammate_branch'], ENT_QUOTES, 'UTF-8') . '<br>' .
            htmlspecialchars($studentData['teammate_college'], ENT_QUOTES, 'UTF-8') . '</span></div>';
    }

    $htmlBody = '<!doctype html><html><body style="margin:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#334155;">' .
        '<div style="max-width:680px;margin:24px auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">' .
        '<div style="padding:34px 30px;background:linear-gradient(135deg,#075985,#0e7490 52%,#e11d48);color:#ffffff;">' .
        '<div style="font-size:13px;letter-spacing:2px;text-transform:uppercase;opacity:.9;">R.V.R. &amp; J.C. College of Engineering</div>' .
        '<h1 style="margin:12px 0 2px;font-size:38px;line-height:1.1;font-family:Georgia,serif;">COGNOS 2K26</h1>' .
        '<div style="font-size:18px;font-style:italic;letter-spacing:1px;">Let the data speak</div></div>' .
        '<div style="padding:30px;">' .
        '<p style="font-size:18px;margin:0 0 10px;color:#0f172a;">Dear <strong>' . $safeName . '</strong>,</p>' .
        '<p style="line-height:1.7;margin:0 0 22px;">Greetings from the COGNOS 2K26 Organizing Committee. Your registration is confirmed.</p>' .
        '<div style="padding:18px;background:#eff6ff;border-radius:10px;border-top:4px solid #2563eb;">' .
        '<div style="font-size:12px;letter-spacing:1px;color:#1d4ed8;font-weight:bold;text-transform:uppercase;">Registration details</div>' .
        '<p style="line-height:1.8;margin:8px 0 0;"><strong>Registration ID:</strong> ' . $safeRegCode . '<br><strong>Roll Number:</strong> ' . $safeRollNo . '<br><strong>College:</strong> ' . $safeCollege . '<br><strong>Accommodation Group:</strong> ' . htmlspecialchars($studentData['gender'] ?? '', ENT_QUOTES, 'UTF-8') . '<br><strong>Distance:</strong> ' . htmlspecialchars((string)($studentData['distance_from_college_km'] ?? ''), ENT_QUOTES, 'UTF-8') . ' km<br><strong>Accommodation:</strong> ' . (!empty($studentData['accommodation_required']) ? 'Requested (subject to availability)' : 'Not requested') . '<br><strong>Primary Vishleshana:</strong> ' . (!empty($studentData['primary_vishleshana']) ? 'Participating' : 'Not participating') . '<br><strong>Teammate Vishleshana:</strong> ' . (!empty($studentData['teammate_vishleshana']) ? 'Participating separately' : 'Not participating') . '<br><strong>Date:</strong> Friday, October 9, 2026</p></div>' .
        $teammateHtml .
        '<h2 style="font-size:20px;color:#0f172a;margin:26px 0 12px;">Your registered challenges</h2>' .
        $challengesHtml .
        '<div style="margin-top:22px;padding:18px;background:#fdf2f8;border-radius:10px;border-top:4px solid #db2777;">' .
        '<strong style="color:#be185d;">Join the official WhatsApp community</strong>' .
        '<p style="margin:8px 0 12px;line-height:1.6;">Get real-time announcements, schedules, and lab allotments.</p>' .
        '<a href="' . $safeWaLink . '" style="display:inline-block;padding:11px 18px;background:#16a34a;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;">Join WhatsApp Group</a></div>' .
        '<h2 style="font-size:20px;color:#0f172a;margin:26px 0 12px;">Important guidelines</h2>' .
        '<p style="line-height:1.8;margin:0;">Carry your original college ID card. Report at least 30 minutes before your event. Bring a laptop with the required BI / analytics tools for Data Dazzle.</p>' .
        '<p style="line-height:1.7;margin:24px 0 0;">Warm regards,<br><strong>Organizing Team - COGNOS 2K26</strong><br>Department of Computer Science and Engineering (Data Science)<br>Artificial Intelligence and Data Science (AIDS)<br>' . OFFICIAL_EMAIL . '</p>' .
        '</div><div style="padding:16px 30px;background:#0f172a;color:#cbd5e1;text-align:center;font-size:12px;">COGNOS 2K26 | Let the data speak</div></div></body></html>';

    $body = "Dear {$studentCaps},\n\n" .
        "Greetings from the COGNOS 2K26 Organizing Committee!\n\n" .
        "Thank you for registering for COGNOS 2K26, the National-Level Technical Symposium organized by the Department of CSD & AIDS. We are excited to confirm your participation.\n\n" .
        "--- REGISTRATION DETAILS ---\n" .
        "• Name: {$studentData['student_name']}\n" .
        "• Registration ID: {$regCode}\n" .
        "• Roll Number: {$studentData['roll_no']}\n" .
        "• College: {$studentData['college_name']}\n" .
        "• Accommodation Group: " . ($studentData['gender'] ?? '') . "\n" .
        "• Distance from College: " . ($studentData['distance_from_college_km'] ?? '') . " km\n" .
        "• Accommodation: " . (!empty($studentData['accommodation_required']) ? 'Requested (subject to availability)' : 'Not requested') . "\n" .
        "• Primary Vishleshana: " . (!empty($studentData['primary_vishleshana']) ? 'Participating' : 'Not participating') . "\n" .
        "• Teammate Vishleshana: " . (!empty($studentData['teammate_vishleshana']) ? 'Participating separately' : 'Not participating') . "\n" .
        "• Date: Friday, October 9, 2026\n\n" .
        $teammateText .
        "--- OFFICIAL WHATSAPP COMMUNITY ---\n" .
        "Please join our WhatsApp group for real-time announcements, schedules, and lab allotments:\n" .
        "{$waLink}\n\n" .
        "--- YOUR REGISTERED CHALLENGES ---\n" .
        trim($challengesText) . "\n\n" .
        "--- IMPORTANT GUIDELINES ---\n" .
        "Identification: Carrying your original College ID card is mandatory.\n\n" .
        "Reporting: Please arrive at the registration desk at least 30 minutes prior to your event start time.\n\n" .
        "Razzle Review Submission: Email your IEEE format research paper to " . OFFICIAL_EMAIL . " with subject line: \"[Razzle Review - {$regCode}]\".\n\n" .
        "Data Dazzle Setup: Participants must bring their own laptops with necessary BI / Analytics tools pre-installed.\n\n" .
        "--- KEY CONTACTS ---\n" .
        "• Faculty Coordinators: Dr. Ch. Suneetha (9704118784) | Mr. K. Sai Prasanth (9030232749)\n" .
        "• Student Coordinators: Mr. Uday Chalamala (9542524508) | Ms. A. V. Hema Nandini\n" .
        "• Accommodation & Hospitality: Mr. K. Medeswararao (9885686721)\n\n" .
        "Warm regards,\n" .
        "Organizing Team – COGNOS 2K26\n" .
        "Department of CSD & AIDS\n" .
        "Email: " . OFFICIAL_EMAIL . "\n";

    // =========================================================================
    // DISPATCH ROUTING
    // =========================================================================

    // Driver 1: Google Apps Script Web App (Gmail over HTTPS Port 443 - Works on Render)
    if (defined('GMAIL_WEBHOOK_URL') && !empty(GMAIL_WEBHOOK_URL)) {
        return send_via_google_apps_script(GMAIL_WEBHOOK_URL, $toEmail, $toName, $teammateEmail, $subject, $htmlBody, $body);
    }

    // Driver 2: Brevo REST API (HTTPS Port 443 - Works on Render)
    if (defined('BREVO_API_KEY') && !empty(BREVO_API_KEY)) {
        return send_via_brevo_api(BREVO_API_KEY, $toEmail, $toName, $teammateEmail, $subject, $htmlBody, $body);
    }

    // Driver 3: Resend REST API (HTTPS Port 443 - Works on Render)
    if (defined('RESEND_API_KEY') && !empty(RESEND_API_KEY)) {
        return send_via_resend_api(RESEND_API_KEY, $toEmail, $toName, $teammateEmail, $subject, $htmlBody, $body);
    }

    // Driver 4: Standard PHPMailer SMTP (Fallback for local environments)
    if (SMTP_PASSWORD === 'YOUR_GMAIL_APP_PASSWORD' || empty(SMTP_PASSWORD)) {
        return [
            'sent' => false,
            'message' => 'Email dispatch skipped: Gmail App Password / HTTPS API key is not configured.'
        ];
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = (SMTP_SECURE === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 5;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        if (!empty($teammateEmail) && filter_var($teammateEmail, FILTER_VALIDATE_EMAIL)) {
            $mail->addCC($teammateEmail, $studentData['teammate_name'] ?? 'Teammate');
        }
        $mail->addReplyTo(OFFICIAL_EMAIL, SMTP_FROM_NAME);

        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $htmlBody;
        $mail->AltBody = $body;

        $mail->send();
        return [
            'sent' => true,
            'message' => 'Registration pass delivered successfully to ' . $toEmail
        ];
    } catch (Exception $e) {
        error_log('SMTP Dispatch Error: ' . $mail->ErrorInfo);
        return [
            'sent' => false,
            'message' => 'SMTP blocked or failed: ' . $mail->ErrorInfo . '. Use GMAIL_WEBHOOK_URL on Render.'
        ];
    }
}

// -----------------------------------------------------------------------------
// HTTPS Email Drivers (Never blocked by cloud hosts)
// -----------------------------------------------------------------------------

function send_via_google_apps_script($url, $toEmail, $toName, $ccEmail, $subject, $htmlBody, $textBody) {
    $payload = json_encode([
        'to'       => $toEmail,
        'toName'   => $toName,
        'cc'       => $ccEmail,
        'subject'  => $subject,
        'htmlBody' => $htmlBody,
        'textBody' => $textBody,
        'fromName' => SMTP_FROM_NAME,
        'replyTo'  => OFFICIAL_EMAIL
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['sent' => false, 'message' => "Google Apps Script error: $curlErr"];
    }

    $json = json_decode($response, true);
    if ($httpCode >= 200 && $httpCode < 300 && (!isset($json['success']) || $json['success'] === true)) {
        return ['sent' => true, 'message' => "Email sent via Google Apps Script (Gmail) to $toEmail"];
    }

    return ['sent' => false, 'message' => "Google Apps Script response: $response"];
}

function send_via_brevo_api($apiKey, $toEmail, $toName, $ccEmail, $subject, $htmlBody, $textBody) {
    $to = [['email' => $toEmail, 'name' => $toName]];
    $payload = [
        'sender' => ['name' => SMTP_FROM_NAME, 'email' => SMTP_FROM_EMAIL],
        'to' => $to,
        'subject' => $subject,
        'htmlContent' => $htmlBody,
        'textContent' => $textBody,
        'replyTo' => ['email' => OFFICIAL_EMAIL, 'name' => SMTP_FROM_NAME]
    ];
    if (!empty($ccEmail) && filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
        $payload['cc'] = [['email' => $ccEmail]];
    }

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'accept: application/json',
            'api-key: ' . $apiKey,
            'content-type: application/json'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['sent' => false, 'message' => "Brevo API error: $curlErr"];
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        return ['sent' => true, 'message' => "Email delivered via Brevo API to $toEmail"];
    }

    return ['sent' => false, 'message' => "Brevo API error (HTTP $httpCode): $response"];
}

function send_via_resend_api($apiKey, $toEmail, $toName, $ccEmail, $subject, $htmlBody, $textBody) {
    $payload = [
        'from' => SMTP_FROM_NAME . ' <onboarding@resend.dev>',
        'to' => [$toEmail],
        'subject' => $subject,
        'html' => $htmlBody,
        'text' => $textBody,
        'reply_to' => OFFICIAL_EMAIL
    ];
    if (!empty($ccEmail) && filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
        $payload['cc'] = [$ccEmail];
    }

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return ['sent' => true, 'message' => "Email delivered via Resend API to $toEmail"];
    }
    return ['sent' => false, 'message' => "Resend API error (HTTP $httpCode): $response"];
}
