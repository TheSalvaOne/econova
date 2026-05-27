<?php
// pages/carrito-update.php — AJAX endpoint actualizar cantidad
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion();

header('Content-Type: application/json');

if (!usuario_logueado()) {
    echo json_encode(['ok' => false]); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false]); exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!hash_equals(csrf_token(), $token)) {
    http_response_code(403); echo json_encode(['ok' => false]); exit;
}

$producto_id = sanitize_int($_POST['producto_id'] ?? 0);
$cantidad    = max(1, sanitize_int($_POST['cantidad'] ?? 1));
$uid         = (int)$_SESSION['usuario_id'];

// Verificar stock
$stock = db()->prepare('SELECT stock FROM productos WHERE id=? AND activo=1');
$stock->execute([$producto_id]);
$prod = $stock->fetch();
if (!$prod) { echo json_encode(['ok' => false]); exit; }

$cantidad = min($cantidad, $prod['stock']);

db()->prepare('UPDATE carrito SET cantidad=? WHERE usuario_id=? AND producto_id=?')
   ->execute([$cantidad, $uid, $producto_id]);

// Total items en carrito
$total = db()->prepare('SELECT COALESCE(SUM(cantidad),0) FROM carrito WHERE usuario_id=?');
$total->execute([$uid]);
$total_items = (int)$total->fetchColumn();

echo json_encode(['ok' => true, 'total_items' => $total_items]);
