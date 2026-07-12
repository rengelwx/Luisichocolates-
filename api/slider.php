<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/DB.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$db = new DB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $images = $db->query("SELECT * FROM slider_images WHERE activo = 1 ORDER BY orden ASC");
        jsonResponse($images);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['imagen'])) {
            jsonResponse(['error' => 'URL de imagen requerida'], 400);
        }
        $stmt = $db->prepare("INSERT INTO slider_images (imagen, orden, activo) VALUES (:imagen, :orden, :activo)");
        $stmt->bindValue(':imagen', $input['imagen']);
        $stmt->bindValue(':orden', $input['orden'] ?? 0, SQLITE3_INTEGER);
        $stmt->bindValue(':activo', $input['activo'] ?? 1, SQLITE3_INTEGER);
        $stmt->execute();
        jsonResponse(['id' => $db->lastInsertRowID(), 'message' => 'Imagen agregada'], 201);
        break;

    case 'PUT':
        if (!isset($_GET['id'])) jsonResponse(['error' => 'ID requerido'], 400);
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) jsonResponse(['error' => 'Datos inválidos'], 400);
        $fields = [];
        $params = [':id' => $_GET['id']];
        foreach (['imagen', 'orden', 'activo'] as $f) {
            if (isset($input[$f])) {
                $fields[] = "$f = :$f";
                $params[":$f"] = $input[$f];
            }
        }
        if (empty($fields)) jsonResponse(['error' => 'Sin campos'], 400);
        $sql = "UPDATE slider_images SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, is_int($v) ? SQLITE3_INTEGER : SQLITE3_TEXT);
        }
        $stmt->execute();
        jsonResponse(['message' => 'Actualizado']);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) jsonResponse(['error' => 'ID requerido'], 400);
        $stmt = $db->prepare("DELETE FROM slider_images WHERE id = :id");
        $stmt->bindValue(':id', $_GET['id'], SQLITE3_INTEGER);
        $stmt->execute();
        jsonResponse(['message' => 'Eliminado']);
        break;

    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
}