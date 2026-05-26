<?php
// admin/productos.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion(); require_admin();

// Desactivar/activar producto
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['toggle_activo'])) {
    csrf_verify();
    $pid = sanitize_int($_POST['producto_id']);
    db()->prepare('UPDATE productos SET activo = NOT activo WHERE id=?')->execute([$pid]);
    audit('producto_toggle', 'productos', $pid);
    header('Location: ' . BASE_URL . '/admin/productos.php'); exit;
}

$busq  = trim(strip_tags($_GET['q'] ?? ''));
$where = $busq ? ['p.nombre LIKE ?'] : [];
$pars  = $busq ? ['%'.$busq.'%'] : [];

$productos = db()->prepare(
    "SELECT p.*, c.nombre AS cat_nombre
     FROM productos p JOIN categorias c ON p.categoria_id=c.id
     " . ($where ? 'WHERE '.implode(' AND ',$where) : '') . "
     ORDER BY p.id DESC"
);
$productos->execute($pars);
$lista = $productos->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Productos — Admin EcoNova</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <a href="<?= BASE_URL ?>/index.php" class="logo"><span class="logo-eco">Eco</span><span class="logo-nova" style="color:#fff">Nova</span></a>
    <nav class="admin-nav">
      <a href="<?= BASE_URL ?>/admin/index.php">📊 Dashboard</a>
      <a href="<?= BASE_URL ?>/admin/productos.php" class="active">📦 Productos</a>
      <a href="<?= BASE_URL ?>/admin/presupuestos.php">📋 Presupuestos</a>
      <a href="<?= BASE_URL ?>/admin/usuarios.php">👥 Usuarios</a>
      <a href="<?= BASE_URL ?>/admin/audit.php">🔍 Audit log</a>
      <hr style="border-color:rgba(255,255,255,.1); margin:1rem 0">
      <a href="<?= BASE_URL ?>/index.php">🌐 Ver web</a>
      <a href="<?= BASE_URL ?>/pages/logout.php">🚪 Cerrar sesión</a>
    </nav>
  </aside>
  <div class="admin-content">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem">
      <h1>Productos</h1>
      <form method="GET" style="display:flex; gap:.5rem">
        <input type="search" name="q" value="<?= e($busq) ?>" placeholder="Buscar producto..."
               style="padding:.5rem .875rem; border:1px solid var(--borde); border-radius:4px; font-family:var(--font-body)">
        <button type="submit" class="btn btn-outline btn-sm">Buscar</button>
      </form>
    </div>

    <div class="admin-card">
      <table class="tabla-admin">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Grado</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista as $p): ?>
            <tr <?= !$p['activo'] ? 'style="opacity:.5"' : '' ?>>
              <td style="font-size:.8rem; color:var(--gris-medio)">#<?= $p['id'] ?></td>
              <td style="max-width:220px">
                <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $p['id'] ?>"
                   target="_blank" style="font-weight:600; font-size:.875rem">
                  <?= e($p['nombre']) ?>
                </a>
              </td>
              <td style="font-size:.85rem"><?= e($p['cat_nombre']) ?></td>
              <td style="font-weight:700; color:var(--naranja)"><?= number_format($p['precio'],2,',','.') ?> €</td>
              <td><?= $p['stock'] ?></td>
              <td><span class="badge badge-grado-<?= strtolower($p['grado']) ?>">Grado <?= e($p['grado']) ?></span></td>
              <td>
                <?php if ($p['activo']): ?>
                  <span class="badge" style="background:#D4F0D4; color:#1A6B1A">Activo</span>
                <?php else: ?>
                  <span class="badge" style="background:#FFE5E5; color:#8B1A1A">Inactivo</span>
                <?php endif; ?>
              </td>
              <td>
                <form method="POST" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                  <button name="toggle_activo" value="1" class="btn btn-outline btn-sm"
                          onclick="return confirm('¿Cambiar estado del producto?')">
                    <?= $p['activo'] ? 'Desactivar' : 'Activar' ?>
                  </button>
                </form>
                <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $p['id'] ?>"
                   target="_blank" class="btn btn-ghost btn-sm">Ver →</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
