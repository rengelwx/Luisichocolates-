<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/DB.php';

$db = new DB();
$rows = $db->query("SELECT section, `key`, value FROM site_config");
$config = [];
foreach ($rows as $row) {
    $config[$row['section']][$row['key']] = $row['value'];
}
jsonResponse($config);