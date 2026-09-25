<?php
/**
 * COGNOS 2K26 - Central Configuration File
 * Easily customizable for future fest editions (e.g. COGNOS 2K27)
 */

// Prevent multiple inclusions
if (!defined('COGNOS_CONFIG_LOADED')) {
    define('COGNOS_CONFIG_LOADED', true);

    // Error reporting (set to 0 in production)
    error_reporting(E_ALL);
    ini_set('display_errors', '0');

    // --------------------------------------------------------------------------
    // 1. Database Configuration (Supports Render Environment Variables with TiDB Cloud)
    // --------------------------------------------------------------------------
    define('DB_HOST', getenv('DB_HOST') ?: 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com');
    define('DB_PORT', getenv('DB_PORT') ?: '4000');
    define('DB_NAME', getenv('DB_NAME') ?: 'cognos_2k26');
    define('DB_USER', getenv('DB_USER') ?: '3BuQVnU4HMDDSUm.root');
    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'MN7h48nJBBriH95D');
    define('DB_CHARSET', 'utf8mb4');

    // --------------------------------------------------------------------------
    // 2. Email Service Configuration (HTTPS Relay & SMTP Fallback)
    // Note: Render free tier blocks outbound raw SMTP ports (25, 465, 587).
    // On Render, emails are delivered over HTTPS (port 443) via Google Apps Script Relay or Brevo/Resend API.
    // --------------------------------------------------------------------------
    define('GMAIL_WEBHOOK_URL', getenv('GMAIL_WEBHOOK_URL') ?: ''); // Free Google Apps Script Web App URL
    define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '');         // Optional Brevo REST API Key
    define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: '');       // Optional Resend REST API Key

    define('SMTP_HOST', 'smtp.gmail.com');
    define('SMTP_PORT', 587);
    define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'
    define('SMTP_USERNAME', 'cognos.csd@gmail.com');       // Your Gmail address
    define('SMTP_PASSWORD', 'tznnslqimibbmxlf');   // 16-character Gmail App Password
    define('SMTP_FROM_EMAIL', 'cognos.csd@gmail.com');
    define('SMTP_FROM_NAME', 'COGNOS 2K26 - CSD & AIDS');

    // --------------------------------------------------------------------------
    // 3. Fest & Community Details
    // --------------------------------------------------------------------------
    define('FEST_NAME', 'COGNOS 2K26');
    define('FEST_TAGLINE', 'LET THE DATA SPEAK');
    define('FEST_DEPT', 'Department of Computer Science & Design (CSD) & Artificial Intelligence & Data Science (AIDS)');
    define('FEST_DATE_TEXT', 'October 9, 2026 (Friday)');
    define('FEST_DATE_ISO', '2026-10-09T09:00:00');
    define('WHATSAPP_COMMUNITY_LINK', 'https://chat.whatsapp.com/CARqCec6NQv5UJGncqCVaT');
    define('OFFICIAL_EMAIL', 'cognos.csd@gmail.com');

    // --------------------------------------------------------------------------
    // 4. Hidden Admin Credentials (Direct access via /admin.php)
    // --------------------------------------------------------------------------
    define('ADMIN_USERNAME', 'admin');
    define('ADMIN_PASSWORD', 'admin@cognos2026'); // Change this as needed

    // --------------------------------------------------------------------------
    // 5. File Upload Settings (College ID Cards)
    // --------------------------------------------------------------------------
    define('UPLOAD_DIR', __DIR__ . '/uploads/id_cards/');
    define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
    define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'webp']);

    // --------------------------------------------------------------------------
    // 6. Event Metadata & Contact Info
    // --------------------------------------------------------------------------
    $COGNOS_EVENTS = [
        'Vishleshana' => [
            'name' => 'Vishleshana',
            'type' => 'Group Discussion Rounds',
            'date_time' => 'October 9, Friday · 2:00 – 5:00 PM',
            'team_size' => 'Individual',
            'venue' => 'Panel 1: CM SCE Lab / Panel 2: Dassault Systemes Lab, Decennial Block – III Floor',
            'prize_pool' => '₹6,000 (🥇 ₹3,000 | 🥈 ₹2,000 | 🥉 ₹1,000)',
            'faculty' => 'Dr. Ganji Ramanjaiah (9848332853), Mr. A V Krishnarao Padyala (9491016925)',
            'students' => 'M. Pardha Saradhi (8019998439), K. Madhu Sudhan (8121160316)'
        ],
        'Razzle Review' => [
            'name' => 'Razzle Review',
            'type' => 'Paper Presentation',
            'date_time' => 'October 9, Friday · From 11:00 AM onwards',
            'team_size' => 'Max 2 Members',
            'venue' => 'CM SCE Lab, Decennial Block – III Floor',
            'prize_pool' => '₹6,000 (🥇 ₹3,000 | 🥈 ₹2,000 | 🥉 ₹1,000)',
            'faculty' => 'Dr. Ch. Sudha Sree (9494641234), Dr. Subramanyam Kunisetti (9441065060)',
            'students' => 'Pangala Tarun (9959084678), Shaik Zaheer (7989863901)'
        ],
        'Data Dazzle' => [
            'name' => 'Data Dazzle',
            'type' => 'Data Storytelling & Dashboard Presentation',
            'date_time' => 'October 9, Friday · 1:00 – 3:00 PM',
            'team_size' => '2 Members',
            'venue' => 'Dassault Systemes Lab, Decennial Block – III Floor',
            'prize_pool' => '₹6,000 (🥇 ₹3,000 | 🥈 ₹2,000 | 🥉 ₹1,000)',
            'faculty' => 'Dr. R. V. Kishore Kumar (9885993494), Mr. Rallabandi Ch S N P Sairam (8328505878)',
            'students' => 'Aparna Sahu (9392854512), Kolla Kesava Chandi Kumar (8247396774)'
        ]
    ];

    // Helper function for sending CORS headers (allows decoupled frontend)
    function apply_cors_headers() {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
        } else {
            header("Access-Control-Allow-Origin: *");
        }
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, " . ($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? ''));
            header('Access-Control-Max-Age: 86400');
            exit(0);
        }
    }
}
