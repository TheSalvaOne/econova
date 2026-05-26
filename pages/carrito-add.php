<?php
// pages/carrito-add.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . '/pages/catalogo.php'); exit; }
csrf_verify();

$producto_id = sanitize_int($_POST['producto_id'] ?? 0);
$redirect    = $_POST['redirect'] ?? BASE_URL . '/pages/carrito.php';
$usuario_id  = (int)$_SESSION['usuario_id'];

// Verificar producto
$check = db()->prepare('SELECT stock FROM productos WHERE id=? AND activo=1');
$check->execute([$producto_id]);
$prod = $check->fetch();
if (!$prod || $prod['stock'] < 1) { header('Location: ' . BASE_URL . '/pages/catalogo.php'); exit; }

// Insert or increment
db()->prepare(
    'INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?,?,1)
     ON DUPLICATE KEY UPDATE cantidad = LEAST(cantidad+1, ?)'
)->execute([$usuario_id, $producto_id, $prod['stock']]);

audit('carrito_add', 'productos', $producto_id);
header('Location: ' . $redirect); exit;
