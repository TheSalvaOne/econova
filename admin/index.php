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
      <a href="<?= BASE_URL ?>/admin/index.php" class="active"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg> Dashboard</a>
      <a href="<?= BASE_URL ?>/admin/productos.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg> Productos</a>
      <a href="<?= BASE_URL ?>/admin/presupuestos.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg> Presupuestos</a>
      <a href="<?= BASE_URL ?>/admin/usuarios.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Usuarios</a>
      <a href="<?= BASE_URL ?>/admin/audit.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg> Audit log</a>
      <hr style="border-color:rgba(255,255,255,.1); margin:1rem 0">
      <a href="<?= BASE_URL ?>/index.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> Ver web</a>
      <a href="<?= BASE_URL ?>/pages/logout.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Cerrar sesión</a>
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
