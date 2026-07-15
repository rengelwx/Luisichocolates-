<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/DB.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['section']) || !isset($input['key']) || !isset($input['value'])) {
    jsonResponse(['error' => 'Faltan campos: section, key, value'], 400);
}

$section = $input['section'];
$key = $input['key'];
$value = $input['value'];

$db = new DB();
$stmt = $db->prepare('INSERT INTO site_config (section, "key", value) VALUES (:section, :key, :value)
    ON CONFLICT(section, "key") DO UPDATE SET value = :value2');
$stmt->bindValue(':section', $section);
$stmt->bindValue(':key', $key);
$stmt->bindValue(':value', $value);
$stmt->bindValue(':value2', $value);
$stmt->execute();

jsonResponse(['success' => true]);