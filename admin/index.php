<?php
// admin/index.php — Panel de administración
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion(); require_admin();

// Stats
$stats = [
    'productos'    => db()->query('SELECT COUNT(*) FROM productos WHERE activo=1')->fetchColumn(),
    'usuarios'     => db()->query('SELECT COUNT(*) FROM usuarios WHERE rol="cliente"')->fetchColumn(),
    'presupuestos' => db()->query('SELECT COUNT(*) FROM presupuestos')->fetchColumn(),
    'pendientes'   => db()->query('SELECT COUNT(*) FROM presupuestos WHERE estado="pendiente"')->fetchColumn(),
];

// Últimos presupuestos
$pres_stmt = db()->query(
    "SELECT p.*, u.nombre AS usuario_nombre, u.email AS usuario_email
     FROM presupuestos p JOIN usuarios u ON p.usuario_id=u.id
     ORDER BY p.created_at DESC LIMIT 10"
);
$presupuestos = $pres_stmt->fetchAll();

$page_title = 'Panel Admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin — <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <a href="<?= BASE_URL ?>/index.php" class="logo">
      <span class="logo-eco">Eco</span><span class="logo-nova" style="color:#fff">Nova</span>
    </a>
    <nav class="admin-nav">
      <a href="<?= BASE_URL ?>/admin/index.php" class="active">📊 Dashboard</a>
      <a href="<?= BASE_URL ?>/admin/productos.php">📦 Productos</a>
      <a href="<?= BASE_URL ?>/admin/presupuestos.php">📋 Presupuestos</a>
      <a href="<?= BASE_URL ?>/admin/usuarios.php">👥 Usuarios</a>
      <a href="<?= BASE_URL ?>/admin/audit.php">🔍 Audit log</a>
      <hr style="border-color:rgba(255,255,255,.1); margin:1rem 0">
      <a href="<?= BASE_URL ?>/index.php">🌐 Ver web</a>
      <a href="<?= BASE_URL ?>/pages/logout.php">🚪 Cerrar sesión</a>
    </nav>
  </aside>

  <!-- Contenido -->
  <div class="admin-content">
    <div style="margin-bottom:2rem">
      <h1>Dashboard</h1>
      <p style="color:var(--gris-medio)">Hola, <?= e($_SESSION['usuario_nombre']) ?> · <?= date('d/m/Y') ?></p>
    </div>

    <!-- Stats -->
    <div class="admin-stats-grid">
      <div class="admin-stat-card">
        <div class="admin-stat-num"><?= $stats['productos'] ?></div>
        <div style="font-size:.85rem; color:var(--gris-medio)">Productos activos</div>
      </div>
      <div class="admin-stat-card">
        <div class="admin-stat-num"><?= $stats['usuarios'] ?></div>
        <div style="font-size:.85rem; color:var(--gris-medio)">Clientes registrados</div>
      </div>
      <div class="admin-stat-card">
        <div class="admin-stat-num"><?= $stats['presupuestos'] ?></div>
        <div style="font-size:.85rem; color:var(--gris-medio)">Presupuestos totales</div>
      </div>
      <div class="admin-stat-card" style="border-left:3px solid var(--naranja)">
        <div class="admin-stat-num"><?= $stats['pendientes'] ?></div>
        <div style="font-size:.85rem; color:var(--gris-medio)">Pendientes de revisar</div>
      </div>
    </div>

    <!-- Últimos presupuestos -->
    <div class="admin-card">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem">
        <h3>Últimas solicitudes de presupuesto</h3>
        <a href="<?= BASE_URL ?>/admin/presupuestos.php" style="font-size:.85rem; color:var(--naranja)">Ver todos →</a>
      </div>

      <?php if (empty($presupuestos)): ?>
        <p style="color:var(--gris-medio); text-align:center; padding:2rem">Sin presupuestos aún.</p>
      <?php else: ?>
        <table class="tabla-admin">
          <thead>
            <tr>
              <th>#</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Total</th>
              <th>Estado</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($presupuestos as $p): ?>
              <tr>
                <td><strong>#<?= $p['id'] ?></strong></td>
                <td>
                  <?= e($p['usuario_nombre']) ?>
                  <br><small style="color:var(--gris-medio)"><?= e($p['usuario_email']) ?></small>
                </td>
                <td style="font-size:.85rem"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                <td style="font-weight:700; color:var(--naranja)"><?= number_format($p['total'],2,',','.') ?> €</td>
                <td><span class="estado-badge estado-<?= e($p['estado']) ?>"><?= e(ucfirst($p['estado'])) ?></span></td>
                <td>
                  <a href="<?= BASE_URL ?>/admin/presupuesto-detalle.php?id=<?= $p['id'] ?>"
                     style="color:var(--naranja); font-size:.85rem">Ver →</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>
