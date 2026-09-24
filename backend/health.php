<?php
/**
 * Render Health Check Endpoint
 * Returns HTTP 200 to indicate the service is running.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';
apply_cors_headers();

$response = [
    'status' => 'ok',
    'service' => 'COGNOS 2K26 API Backend',
    'timestamp' => date('c'),
    'php_version' => PHP_VERSION,
];

// Optional quick DB check
if (isset($_GET['check_db'])) {
    try {
        require_once __DIR__ . '/db.php';
        $pdo = get_db_connection();
        $response['database'] = 'connected';
    } catch (Exception $e) {
        $response['database'] = 'disconnected';
        $response['db_error'] = $e->getMessage();
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);
