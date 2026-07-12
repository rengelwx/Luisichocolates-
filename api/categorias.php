<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/DB.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$db = new DB();
$result = $db->query("SELECT c.*, (SELECT COUNT(*) FROM productos p WHERE p.categoria_id = c.id AND p.activo = 1) as total_productos FROM categorias c ORDER BY c.nombre");
jsonResponse($result);