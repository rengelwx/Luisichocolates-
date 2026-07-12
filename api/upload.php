<?php
require_once __DIR__ . '/../config.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['imagen'])) {
    jsonResponse(['error' => 'Archivo requerido'], 400);
}

$file = $_FILES['imagen'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(['error' => 'Error subiendo archivo'], 400);
}

$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
if (!in_array($file['type'], $allowed)) {
    jsonResponse(['error' => 'Tipo no permitido (jpg, png, webp, gif)'], 400);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$name = uniqid('img_') . '.' . $ext;
$dest = UPLOAD_DIR . $name;

if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    jsonResponse(['error' => 'No se pudo mover archivo'], 500);
}

jsonResponse(['filename' => $name, 'url' => SITE_URL . '/uploads/' . $name]);