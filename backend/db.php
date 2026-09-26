<?php
/**
 * COGNOS 2K26 - Database Connection Handler
 * Uses PDO for robust and secure database interactions.
 */

require_once __DIR__ . '/config.php';

function get_db_connection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $port = defined('DB_PORT') ? DB_PORT : (getenv('DB_PORT') ?: '4000');
    $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Enable SSL when connecting to cloud MySQL (TiDB Cloud, Aiven, etc.)
    if (getenv('DB_SSL') === 'true' || strpos(DB_HOST, 'tidbcloud.com') !== false) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $pdo->exec("SET time_zone = '+05:30'");
        return $pdo;
    } catch (PDOException $e) {
        // Return JSON error if API context
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please verify the database host, name, username, password, and imported tables in backend/config.php.',
            'error_detail' => $e->getMessage()
        ]);
        exit;
    }
}
