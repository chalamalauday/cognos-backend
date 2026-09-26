<?php
/**
 * COGNOS 2K26 - Admin API Endpoint
 * Provides dashboard metrics, live filtered tables, and record management.
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Check Admin Authentication
if (!isset($_SESSION['cognos_admin_logged_in']) || $_SESSION['cognos_admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
    exit;
}

$pdo = get_db_connection();
$action = $_GET['action'] ?? 'stats';

try {
    if ($action === 'stats') {
        // 1. Total registrations
        $totalReg = (int)$pdo->query("SELECT COUNT(*) FROM `registrations`")->fetchColumn();

        // 2. Event breakdown
        $eventCounts = [];
        foreach (array_keys($COGNOS_EVENTS) as $ev) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `registration_events` WHERE `event_name` = ?");
            $stmt->execute([$ev]);
            $eventCounts[$ev] = (int)$stmt->fetchColumn();
        }

        // 3. Unique colleges
        $uniqueColleges = (int)$pdo->query("SELECT COUNT(DISTINCT `college_name`) FROM `registrations`")->fetchColumn();

        // 4. Teams with teammates
        $teammateCount = (int)$pdo->query("SELECT COUNT(*) FROM `registrations` WHERE `has_teammate` = 1")->fetchColumn();

        $vCount = (int)$pdo->query("SELECT COUNT(*) FROM `registration_participants` WHERE `participates_vishleshana` = 1")->fetchColumn();
        $rCount = $eventCounts['Razzle Review'] ?? 0;
        $dCount = $eventCounts['Data Dazzle'] ?? 0;

        $statsPayload = [
            'total_registrations' => $totalReg,
            'vishleshana' => $vCount,
            'razzle_review' => $rCount,
            'data_dazzle' => $dCount,
            'unique_colleges' => $uniqueColleges,
            'teams_count' => $teammateCount,
            'events_breakdown' => $eventCounts
        ];

        echo json_encode([
            'success' => true,
            'stats' => $statsPayload,
            'total_registrations' => $totalReg,
            'vishleshana' => $vCount,
            'razzle_review' => $rCount,
            'data_dazzle' => $dCount,
            'unique_colleges' => $uniqueColleges,
            'teams_count' => $teammateCount,
            'events_breakdown' => $eventCounts
        ]);
        exit;

    } elseif ($action === 'list') {
        $eventFilter = trim($_GET['event'] ?? 'all');
        $search = trim($_GET['search'] ?? '');

        $sql = "
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
                r.id_card_path,
                r.has_teammate,
                r.teammate_name,
                r.teammate_email,
                r.teammate_roll_no,
                r.teammate_branch,
                r.teammate_college,
                r.created_at,
                GROUP_CONCAT(re.event_name ORDER BY re.event_name SEPARATOR ', ') AS events_list
            FROM `registrations` r
            LEFT JOIN `registration_events` re ON r.id = re.registration_id
        ";

        $conditions = [];
        $params = [];

        if ($eventFilter !== 'all' && isset($COGNOS_EVENTS[$eventFilter])) {
            $conditions[] = "r.id IN (SELECT registration_id FROM `registration_events` WHERE `event_name` = ?)";
            $params[] = $eventFilter;
        }

        if (!empty($search)) {
            $conditions[] = "(r.student_name LIKE ? OR r.roll_no LIKE ? OR r.college_name LIKE ? OR r.reg_code LIKE ? OR r.email LIKE ? OR r.branch LIKE ? OR r.teammate_name LIKE ? OR r.teammate_email LIKE ? OR r.teammate_roll_no LIKE ? OR EXISTS (SELECT 1 FROM `registration_events` search_events WHERE search_events.registration_id = r.id AND search_events.event_name LIKE ?))";
            $term = '%' . $search . '%';
            $params = array_merge($params, [$term, $term, $term, $term, $term, $term, $term, $term, $term, $term]);
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY r.id ORDER BY r.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $registrations = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'count' => count($registrations),
            'registrations' => $registrations
        ]);
        exit;

    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid registration ID.']);
            exit;
        }

        // Fetch ID card path to delete file
        $stmt = $pdo->prepare("SELECT id_card_path FROM `registrations` WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row && !empty($row['id_card_path'])) {
            $fullFilePath = __DIR__ . '/' . $row['id_card_path'];
            if (file_exists($fullFilePath)) {
                unlink($fullFilePath);
            }
        }

        // Delete from database (Cascades to registration_events)
        $delStmt = $pdo->prepare("DELETE FROM `registrations` WHERE id = ?");
        $delStmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Registration deleted successfully.']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
