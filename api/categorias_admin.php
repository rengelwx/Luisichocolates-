<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/DB.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$db = new DB();
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db->exec("ALTER TABLE categorias ADD COLUMN orden INTEGER DEFAULT 0");
} catch (\Throwable $e) {}

switch ($method) {
    case 'GET':
        try {
            $cats = $db->query("SELECT c.*, (SELECT COUNT(*) FROM productos p WHERE p.categoria_id = c.id) as total_productos FROM categorias c ORDER BY c.orden ASC, c.nombre ASC");
            jsonResponse($cats);
        } catch (\Throwable $e) {
            jsonResponse(['error' => 'Error: ' . $e->getMessage()], 500);
        }
        break;

    case 'POST':
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || empty(trim($input['nombre'] ?? ''))) jsonResponse(['error' => 'Nombre requerido'], 400);
            $slug = !empty($input['slug']) ? strtolower(trim($input['slug'])) : strtolower(str_replace(' ', '-', trim($input['nombre'])));
            $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
            if (empty($slug)) $slug = 'categoria-' . time();
            $baseSlug = $slug;
            $counter = 1;
            while (true) {
                $exists = $db->querySingle("SELECT id FROM categorias WHERE slug = '" . addslashes($slug) . "'");
                if (!$exists) break;
                $slug = $baseSlug . '-' . $counter++;
            }
            $orden = isset($input['orden']) ? (int)$input['orden'] : 0;
            $stmt = $db->prepare("INSERT INTO categorias (nombre, slug, orden) VALUES (:nombre, :slug, :orden)");
            $stmt->bindValue(':nombre', trim($input['nombre']), SQLITE3_TEXT);
            $stmt->bindValue(':slug', $slug, SQLITE3_TEXT);
            $stmt->bindValue(':orden', $orden, SQLITE3_INTEGER);
            $ok = $stmt->execute();
            if ($ok) {
                jsonResponse(['id' => $db->lastInsertRowID(), 'message' => 'Categoría creada'], 201);
            } else {
                jsonResponse(['error' => 'Error al crear categoría'], 500);
            }
        } catch (\Throwable $e) {
            jsonResponse(['error' => 'Error al crear: ' . $e->getMessage()], 500);
        }
        break;

    case 'PUT':
        try {
            if (!isset($_GET['id'])) jsonResponse(['error' => 'ID requerido'], 400);
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) jsonResponse(['error' => 'Datos inválidos'], 400);
            $fields = []; $params = [':id' => $_GET['id']];
            foreach (['nombre', 'slug'] as $f) {
                if (array_key_exists($f, $input)) { $fields[] = "$f = :$f"; $params[":$f"] = $input[$f]; }
            }
            if (array_key_exists('orden', $input)) {
                $fields[] = "orden = :orden";
                $params[':orden'] = (int)$input['orden'];
            }
            if (empty($fields)) jsonResponse(['error' => 'Sin campos'], 400);
            $stmt = $db->prepare("UPDATE categorias SET " . implode(', ', $fields) . " WHERE id = :id");
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            jsonResponse(['message' => 'Actualizada']);
        } catch (\Throwable $e) {
            jsonResponse(['error' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
        break;

    case 'DELETE':
        try {
            if (!isset($_GET['id'])) jsonResponse(['error' => 'ID requerido'], 400);
            $check = $db->querySingle("SELECT COUNT(*) FROM productos WHERE categoria_id = " . (int)$_GET['id']);
            if ($check > 0) jsonResponse(['error' => 'No se puede eliminar: tiene productos asociados'], 400);
            $stmt = $db->prepare("DELETE FROM categorias WHERE id = :id");
            $stmt->bindValue(':id', $_GET['id'], SQLITE3_INTEGER);
            $stmt->execute();
            jsonResponse(['message' => 'Eliminada']);
        } catch (\Throwable $e) {
            jsonResponse(['error' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
}
