<?php
// admin/audit.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion(); require_admin();

$logs = db()->query(
    "SELECT al.*, u.nombre AS usuario_nombre
     FROM audit_log al LEFT JOIN usuarios u ON al.usuario_id=u.id
     ORDER BY al.created_at DESC LIMIT 100"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Audit Log — Admin EcoNova</title>
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
      <a href="<?= BASE_URL ?>/admin/productos.php">📦 Productos</a>
      <a href="<?= BASE_URL ?>/admin/presupuestos.php">📋 Presupuestos</a>
      <a href="<?= BASE_URL ?>/admin/usuarios.php">👥 Usuarios</a>
      <a href="<?= BASE_URL ?>/admin/audit.php" class="active">🔍 Audit log</a>
      <hr style="border-color:rgba(255,255,255,.1); margin:1rem 0">
      <a href="<?= BASE_URL ?>/index.php">🌐 Ver web</a>
      <a href="<?= BASE_URL ?>/pages/logout.php">🚪 Cerrar sesión</a>
    </nav>
  </aside>
  <div class="admin-content">
    <div style="margin-bottom:1.5rem">
      <h1>Audit Log</h1>
      <p style="color:var(--gris-medio); font-size:.875rem">Últimas 100 acciones del sistema. Útil para auditorías de seguridad.</p>
    </div>
    <div class="admin-card">
      <table class="tabla-admin">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Acción</th>
            <th>Tabla</th>
            <th>Reg. ID</th>
            <th>IP</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logs as $log): ?>
            <tr>
              <td style="font-size:.78rem; color:var(--gris-medio)"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
              <td style="font-size:.85rem"><?= $log['usuario_nombre'] ? e($log['usuario_nombre']) : '<em style="color:#999">Anónimo</em>' ?></td>
              <td>
                <code style="background:var(--gris-lite); padding:.1rem .4rem; border-radius:3px; font-size:.78rem">
                  <?= e($log['accion']) ?>
                </code>
              </td>
              <td style="font-size:.8rem"><?= e($log['tabla'] ?? '—') ?></td>
              <td style="font-size:.8rem"><?= $log['registro_id'] ?? '—' ?></td>
              <td style="font-size:.78rem; color:var(--gris-medio)"><?= e($log['ip'] ?? '—') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
