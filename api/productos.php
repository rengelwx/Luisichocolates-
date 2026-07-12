<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/DB.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$db = new DB();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $activoFilter = isset($_GET['all']) ? '' : ' AND p.activo = 1';
            $stmt = $db->prepare("SELECT p.*, c.nombre as categoria_nombre
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.id = :id" . $activoFilter);
            $stmt->bindValue(':id', $_GET['id'], SQLITE3_INTEGER);
            $stmt->execute();
            $producto = $stmt->fetchArray();
            if ($producto) {
                jsonResponse($producto);
            }
            jsonResponse(['error' => 'Producto no encontrado'], 404);
        }

        $sql = "SELECT p.*, c.nombre as categoria_nombre
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id";
        if (!isset($_GET['all'])) {
            $sql .= " WHERE p.activo = 1";
        }
        $params = [];

        if (isset($_GET['categoria'])) {
            $sql .= " AND c.slug = :categoria";
            $params[':categoria'] = $_GET['categoria'];
        }
        if (isset($_GET['destacado'])) {
            $sql .= " AND p.destacado = 1";
        }
        if (isset($_GET['search'])) {
            $sql .= " AND (p.nombre LIKE :search OR p.descripcion LIKE :search2)";
            $params[':search'] = '%' . $_GET['search'] . '%';
            $params[':search2'] = '%' . $_GET['search'] . '%';
        }

        $countSql = str_replace("SELECT p.*, c.nombre as categoria_nombre", "SELECT COUNT(*) as total", $sql);
        $countStmt = $db->prepare($countSql);
        foreach ($params as $key => $val) {
            $countStmt->bindValue($key, $val, is_int($val) ? SQLITE3_INTEGER : SQLITE3_TEXT);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchArray()['total'];

        $sql .= " ORDER BY p.destacado DESC, p.created_at DESC";

        if (isset($_GET['limit'])) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = (int)$_GET['limit'];
        }
        if (isset($_GET['offset'])) {
            $sql .= " OFFSET :offset";
            $params[':offset'] = (int)$_GET['offset'];
        }

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? SQLITE3_INTEGER : SQLITE3_TEXT);
        }
        $stmt->execute();

        jsonResponse(['productos' => $stmt->fetchAll(), 'total' => $total]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['nombre']) || (empty($input['precio']) && empty($input['precio_a_convenir']))) {
            jsonResponse(['error' => 'Nombre y precio (o marcar "Precio a convenir") son requeridos'], 400);
        }

        $slug = $input['slug'] ?? strtolower(str_replace(' ', '-', $input['nombre']));
        $stmt = $db->prepare("INSERT INTO productos (nombre, slug, descripcion, precio, precio_oferta, imagen, categoria_id, destacado, precio_a_convenir)
            VALUES (:nombre, :slug, :descripcion, :precio, :precio_oferta, :imagen, :categoria_id, :destacado, :precio_a_convenir)");
        $stmt->bindValue(':nombre', $input['nombre'], SQLITE3_TEXT);
        $stmt->bindValue(':slug', $slug, SQLITE3_TEXT);
        $stmt->bindValue(':descripcion', $input['descripcion'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':precio', $input['precio'], SQLITE3_FLOAT);
        $stmt->bindValue(':precio_oferta', $input['precio_oferta'] ?? null, SQLITE3_FLOAT);
        $stmt->bindValue(':imagen', $input['imagen'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':categoria_id', $input['categoria_id'] ?? null, SQLITE3_INTEGER);
        $stmt->bindValue(':destacado', $input['destacado'] ?? 0, SQLITE3_INTEGER);
        $stmt->bindValue(':precio_a_convenir', $input['precio_a_convenir'] ?? 0, SQLITE3_INTEGER);
        $stmt->execute();

        jsonResponse(['id' => $db->lastInsertRowID(), 'message' => 'Producto creado'], 201);
        break;

    case 'PUT':
        if (!isset($_GET['id'])) {
            jsonResponse(['error' => 'ID requerido'], 400);
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            jsonResponse(['error' => 'Datos inválidos'], 400);
        }

        $fields = [];
        $params = [':id' => $_GET['id']];
        foreach (['nombre', 'slug', 'descripcion', 'precio', 'precio_oferta', 'imagen', 'categoria_id', 'destacado', 'activo', 'precio_a_convenir'] as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $input[$field];
            }
        }
        if (empty($fields)) {
            jsonResponse(['error' => 'Sin campos para actualizar'], 400);
        }

        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $sql = "UPDATE productos SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_float($val) ? SQLITE3_FLOAT : (is_int($val) ? SQLITE3_INTEGER : SQLITE3_TEXT));
        }
        $stmt->execute();
        jsonResponse(['message' => 'Producto actualizado']);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            jsonResponse(['error' => 'ID requerido'], 400);
        }
        $stmt = $db->prepare("DELETE FROM productos WHERE id = :id");
        $stmt->bindValue(':id', $_GET['id'], SQLITE3_INTEGER);
        $stmt->execute();
        jsonResponse(['message' => 'Producto eliminado']);
        break;

    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
}