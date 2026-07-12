<?php
session_start();

define('SITE_NAME', 'Chocolatier Artesanal');
define('SITE_DESC', 'Bombones de chocolate artesanales');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('SITE_URL', 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

function getDB(): PDO|SQLite3 {
    static $db = null;
    if ($db !== null) return $db;

    // Railway PostgreSQL (production)
    $dbUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL') ?? '';
    if ($dbUrl) {
        $dbUrl = str_replace('postgres://', 'postgresql://', $dbUrl);
        $db = new PDO($dbUrl, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $db;
    }

    // Local SQLite (development)
    $sqlitePath = $_ENV['SQLITE_PATH'] ?? (__DIR__ . '/data/database.sqlite');
    $dir = dirname($sqlitePath);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $db = new SQLite3($sqlitePath);
    $db->enableExceptions(true);
    return $db;
}

function jsonResponse($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}