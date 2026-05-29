<?php
// pages/favorito-toggle.php — AJAX endpoint
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion();

header('Content-Type: application/json');

if (!usuario_logueado()) {
    echo json_encode(['ok'=>false, 'redirect'=> BASE_URL . '/pages/login.php']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['ok'=>false]); exit;
}

// CSRF
$token = $_POST['csrf_token'] ?? '';
if (!hash_equals(csrf_token(), $token)) {
    http_response_code(403); echo json_encode(['ok'=>false]); exit;
}

$producto_id = sanitize_int($_POST['producto_id'] ?? 0);
$usuario_id  = (int)$_SESSION['usuario_id'];

// Verificar que el producto existe
$check = db()->prepare('SELECT id FROM productos WHERE id=? AND activo=1');
$check->execute([$producto_id]);
if (!$check->fetch()) { echo json_encode(['ok'=>false]); exit; }

// Toggle
$existe = db()->prepare('SELECT 1 FROM favoritos WHERE usuario_id=? AND producto_id=?');
$existe->execute([$usuario_id, $producto_id]);
$ya_fav = (bool)$existe->fetchColumn();

if ($ya_fav) {
    db()->prepare('DELETE FROM favoritos WHERE usuario_id=? AND producto_id=?')
        ->execute([$usuario_id, $producto_id]);
    $favorito = false;
} else {
    db()->prepare('INSERT IGNORE INTO favoritos (usuario_id, producto_id) VALUES (?,?)')
        ->execute([$usuario_id, $producto_id]);
    $favorito = true;
}

echo json_encode(['ok'=>true, 'favorito'=>$favorito]);
