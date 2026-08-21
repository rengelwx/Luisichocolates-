<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/DB.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$db = new DB();

// Migración: asegurar que exista la columna orden
try {
    $db->exec("ALTER TABLE categorias ADD COLUMN orden INTEGER DEFAULT 0");
} catch (\Throwable $e) {}

$result = $db->query("SELECT c.*, (SELECT COUNT(*) FROM productos p WHERE p.categoria_id = c.id AND p.activo = 1) as total_productos FROM categorias c ORDER BY c.orden ASC, c.nombre ASC");
jsonResponse($result);