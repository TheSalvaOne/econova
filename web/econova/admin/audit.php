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
      <a href="<?= BASE_URL ?>/admin/index.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg> Dashboard</a>
      <a href="<?= BASE_URL ?>/admin/productos.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg> Productos</a>
      <a href="<?= BASE_URL ?>/admin/presupuestos.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg> Presupuestos</a>
      <a href="<?= BASE_URL ?>/admin/usuarios.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Usuarios</a>
      <a href="<?= BASE_URL ?>/admin/audit.php" class="active"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg> Audit log</a>
      <hr style="border-color:rgba(255,255,255,.1); margin:1rem 0">
      <a href="<?= BASE_URL ?>/index.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> Ver web</a>
      <a href="<?= BASE_URL ?>/pages/logout.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Cerrar sesión</a>
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
