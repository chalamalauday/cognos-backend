<?php
/**
 * COGNOS 2K26 - Excel / CSV Export Endpoint
 * Streams UTF-8 formatted CSV for Microsoft Excel with clean event-wise sheets.
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Verify Admin Session
if (!isset($_SESSION['cognos_admin_logged_in']) || $_SESSION['cognos_admin_logged_in'] !== true) {
    http_response_code(403);
    die("Access Denied. Please log in through admin.php.");
}

$pdo = get_db_connection();
$eventFilter = trim($_GET['event'] ?? 'all');

function get_full_id_card_url($path) {
    if (empty($path)) {
        return 'N/A';
    }
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    $protocol = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'cognos.rvrjc.me';
    if (strpos($host, 'cognos.rvrjc.me') !== false) {
        $protocol = 'https';
    }
    $cleanPath = ltrim($path, '/');
    if (strpos($cleanPath, 'backend/') !== 0) {
        $cleanPath = 'backend/' . $cleanPath;
    }
    return "{$protocol}://{$host}/{$cleanPath}";
}

function format_ist_datetime($dtStr) {
    if (empty($dtStr)) return 'N/A';
    $ts = strtotime($dtStr);
    if (!$ts) return $dtStr . ' IST';
    return date('d M Y, h:i A', $ts) . ' IST';
}

$timestamp = date('Ymd_His');

if ($eventFilter === 'Vishleshana') {
    $filename = "COGNOS2K26_Vishleshana_Individual_Participants_{$timestamp}.csv";
    $stmt = $pdo->query("
        SELECT r.reg_code,
               p.participant_type, p.participant_name, p.participant_email, p.roll_no, p.branch, p.college_name, p.created_at
        FROM `registration_participants` p
        INNER JOIN `registrations` r ON r.id = p.registration_id
        WHERE p.participates_vishleshana = 1
        ORDER BY p.id ASC
    ");
    $registrations = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, [
        'Reg Code', 'Participant Type', 'Participant Name', 'Roll No', 'Branch', 'College Name',
        'Email ID', 'Registration Date & Time (IST)'
    ]);

    foreach ($registrations as $row) {
        fputcsv($output, [
            $row['reg_code'], ucfirst($row['participant_type']), $row['participant_name'], $row['roll_no'], $row['branch'],
            $row['college_name'], $row['participant_email'], format_ist_datetime($row['created_at'])
        ]);
    }

    fclose($output);
    exit;

} elseif ($eventFilter !== 'all' && isset($COGNOS_EVENTS[$eventFilter])) {
    $cleanEventName = str_replace(' ', '_', $eventFilter);
    $filename = "COGNOS2K26_{$cleanEventName}_Participants_{$timestamp}.csv";

    // Fetch participants for this specific event
    $stmt = $pdo->prepare("
        SELECT 
            r.reg_code,
            r.student_name,
            r.roll_no,
            r.branch,
            r.college_name,
            r.primary_vishleshana,
            r.teammate_vishleshana,
            r.email,
            r.has_teammate,
            r.teammate_name,
            r.teammate_roll_no,
            r.teammate_branch,
            r.teammate_college,
            r.id_card_path,
            r.created_at
        FROM `registrations` r
        INNER JOIN `registration_events` re ON r.id = re.registration_id
        WHERE re.event_name = ?
        ORDER BY r.id ASC
    ");
    $stmt->execute([$eventFilter]);
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($output, [
        'Reg Code',
        'Student Name',
        'Roll No',
        'Branch',
        'College Name',
        'Primary Vishleshana',
        'Teammate Vishleshana',
        'Email ID',
        'Has Teammate',
        'Teammate Name',
        'Teammate Roll No',
        'Teammate Branch',
        'Teammate College',
        'College ID Card URL',
        'Registration Date & Time (IST)'
    ]);

    foreach ($rows as $row) {
        $idCardUrl = get_full_id_card_url($row['id_card_path'] ?? '');
        fputcsv($output, [
            $row['reg_code'],
            $row['student_name'],
            $row['roll_no'],
            $row['branch'],
            $row['college_name'],
            $row['primary_vishleshana'] ? 'YES' : 'NO',
            $row['teammate_vishleshana'] ? 'YES' : 'NO',
            $row['email'],
            $row['has_teammate'] ? 'YES' : 'NO',
            $row['teammate_name'] ?? 'N/A',
            $row['teammate_roll_no'] ?? 'N/A',
            $row['teammate_branch'] ?? 'N/A',
            $row['teammate_college'] ?? 'N/A',
            $idCardUrl,
            format_ist_datetime($row['created_at'])
        ]);
    }

    fclose($output);
    exit;

} else {
    // Export All Events / Master Sheet
    $filename = "COGNOS2K26_Master_Registrations_{$timestamp}.csv";

    $stmt = $pdo->query("
        SELECT 
            r.id,
            r.reg_code,
            r.student_name,
            r.roll_no,
            r.branch,
            r.college_name,
            r.primary_vishleshana,
            r.teammate_vishleshana,
            r.email,
            r.has_teammate,
            r.teammate_name,
            r.teammate_roll_no,
            r.teammate_branch,
            r.teammate_college,
            r.id_card_path,
            r.created_at,
            GROUP_CONCAT(re.event_name SEPARATOR ', ') AS registered_events
        FROM `registrations` r
        LEFT JOIN `registration_events` re ON r.id = re.registration_id
        GROUP BY r.id
        ORDER BY r.id ASC
    ");
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($output, [
        'ID',
        'Reg Code',
        'Student Name',
        'Roll No',
        'Branch',
        'College Name',
        'Primary Vishleshana',
        'Teammate Vishleshana',
        'Email ID',
        'Events Registered',
        'Has Teammate',
        'Teammate Name',
        'Teammate Roll No',
        'Teammate Branch',
        'Teammate College',
        'College ID Card URL',
        'Registered At (IST)'
    ]);

    foreach ($rows as $row) {
        $idCardUrl = get_full_id_card_url($row['id_card_path'] ?? '');
        fputcsv($output, [
            $row['id'],
            $row['reg_code'],
            $row['student_name'],
            $row['roll_no'],
            $row['branch'],
            $row['college_name'],
            $row['primary_vishleshana'] ? 'YES' : 'NO',
            $row['teammate_vishleshana'] ? 'YES' : 'NO',
            $row['email'],
            $row['registered_events'] ?? 'None',
            $row['has_teammate'] ? 'YES' : 'NO',
            $row['teammate_name'] ?? 'N/A',
            $row['teammate_roll_no'] ?? 'N/A',
            $row['teammate_branch'] ?? 'N/A',
            $row['teammate_college'] ?? 'N/A',
            $idCardUrl,
            format_ist_datetime($row['created_at'])
        ]);
    }

    fclose($output);
    exit;
}
