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
    // 1. Database Configuration (Default XAMPP credentials)
    // --------------------------------------------------------------------------
    define('DB_HOST', 'sql312.infinityfree.com');
    define('DB_NAME', 'if0_42997910_cognos');
    define('DB_USER', 'if0_42997910');
    define('DB_PASS', 'cognos2026');
    define('DB_CHARSET', 'utf8mb4');

    // --------------------------------------------------------------------------
    // 2. Gmail SMTP & PHPMailer Configuration
    // --------------------------------------------------------------------------
    // Replace with your actual Gmail and 16-character App Password
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
    define('WHATSAPP_COMMUNITY_LINK', 'https://chat.whatsapp.com/invite/cognos2k26'); // Update with your actual WhatsApp link
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
            'faculty' => 'Dr. Ganji Ramanjaiah (9848332853), Dr. Riaz Shaik (9966743943)',
            'students' => 'Bonamukkala Ajay (7207039202), Shaik Ayesha Rizwana'
        ],
        'Razzle Review' => [
            'name' => 'Razzle Review',
            'type' => 'Paper Presentation',
            'date_time' => 'October 9, Friday · From 11:00 AM onwards',
            'team_size' => 'Max 2 Members',
            'venue' => 'CM SCE Lab, Decennial Block – III Floor',
            'prize_pool' => '₹6,000 (🥇 ₹3,000 | 🥈 ₹2,000 | 🥉 ₹1,000)',
            'faculty' => 'Dr. Ch. Sudha Sree (9494641234), Dr. Vallabhajosyula Sasikala (7976835016)',
            'students' => 'Ponnaluri Jeyanth (8331927193), N Gayatri'
        ],
        'Data Dazzle' => [
            'name' => 'Data Dazzle',
            'type' => 'Data Storytelling & Dashboard Presentation',
            'date_time' => 'October 9, Friday · 1:00 – 3:00 PM',
            'team_size' => '2 Members',
            'venue' => 'Dassault Systemes Lab, Decennial Block – III Floor',
            'prize_pool' => '₹6,000 (🥇 ₹3,000 | 🥈 ₹2,000 | 🥉 ₹1,000)',
            'faculty' => 'Dr. R. V. Kishore Kumar (9885993494), Mr. Rallabandi Ch S N P Sairam (8328505878)',
            'students' => 'Jarabana Krishna Kanth (7013162268), Sahitya.B'
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
