<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/DB.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$db = new DB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $cats = $db->query("SELECT c.*, (SELECT COUNT(*) FROM productos p WHERE p.categoria_id = c.id AND p.activo = 1) as total_productos FROM categorias c ORDER BY c.nombre");
        jsonResponse($cats);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['nombre'])) jsonResponse(['error' => 'Nombre requerido'], 400);
        $slug = $input['slug'] ?? strtolower(str_replace(' ', '-', $input['nombre']));
        $stmt = $db->prepare("INSERT INTO categorias (nombre, slug) VALUES (:nombre, :slug)");
        $stmt->bindValue(':nombre', $input['nombre']);
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();
        jsonResponse(['id' => $db->lastInsertRowID(), 'message' => 'Categoría creada'], 201);
        break;

    case 'PUT':
        if (!isset($_GET['id'])) jsonResponse(['error' => 'ID requerido'], 400);
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) jsonResponse(['error' => 'Datos inválidos'], 400);
        $fields = []; $params = [':id' => $_GET['id']];
        foreach (['nombre', 'slug'] as $f) {
            if (isset($input[$f])) { $fields[] = "$f = :$f"; $params[":$f"] = $input[$f]; }
        }
        if (empty($fields)) jsonResponse(['error' => 'Sin campos'], 400);
        $stmt = $db->prepare("UPDATE categorias SET " . implode(', ', $fields) . " WHERE id = :id");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        jsonResponse(['message' => 'Actualizada']);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) jsonResponse(['error' => 'ID requerido'], 400);
        $check = $db->querySingle("SELECT COUNT(*) FROM productos WHERE categoria_id = " . (int)$_GET['id']);
        if ($check > 0) jsonResponse(['error' => 'No se puede eliminar: tiene productos asociados'], 400);
        $stmt = $db->prepare("DELETE FROM categorias WHERE id = :id");
        $stmt->bindValue(':id', $_GET['id'], SQLITE3_INTEGER);
        $stmt->execute();
        jsonResponse(['message' => 'Eliminada']);
        break;

    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
}