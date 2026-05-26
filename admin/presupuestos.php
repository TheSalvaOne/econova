<?php
// admin/presupuestos.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion(); require_admin();

$estado_filtro = $_GET['estado'] ?? '';
$estados = ['','pendiente','revisando','aprobado','rechazado'];

$where = $estado_filtro && in_array($estado_filtro, $estados) ? 'WHERE p.estado=?' : '';
$pars  = $estado_filtro ? [$estado_filtro] : [];

$stmt = db()->prepare(
    "SELECT p.*, u.nombre AS unom, u.email AS uemail
     FROM presupuestos p JOIN usuarios u ON p.usuario_id=u.id
     $where ORDER BY p.created_at DESC"
);
$stmt->execute($pars);
$lista = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Presupuestos — Admin EcoNova</title>
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
      <a href="<?= BASE_URL ?>/admin/presupuestos.php" class="active">📋 Presupuestos</a>
      <a href="<?= BASE_URL ?>/admin/usuarios.php">👥 Usuarios</a>
      <a href="<?= BASE_URL ?>/admin/audit.php">🔍 Audit log</a>
      <hr style="border-color:rgba(255,255,255,.1); margin:1rem 0">
      <a href="<?= BASE_URL ?>/index.php">🌐 Ver web</a>
      <a href="<?= BASE_URL ?>/pages/logout.php">🚪 Cerrar sesión</a>
    </nav>
  </aside>
  <div class="admin-content">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem">
      <h1>Presupuestos</h1>
      <div style="display:flex; gap:.5rem; flex-wrap:wrap">
        <?php foreach ([''=>'Todos','pendiente'=>'Pendientes','revisando'=>'Revisando','aprobado'=>'Aprobados','rechazado'=>'Rechazados'] as $v => $l): ?>
          <a href="?estado=<?= $v ?>"
             class="btn btn-sm <?= $estado_filtro===$v ? 'btn-primary' : 'btn-outline' ?>">
            <?= $l ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="admin-card">
      <table class="tabla-admin">
        <thead>
          <tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Estado</th><th>Acción</th></tr>
        </thead>
        <tbody>
          <?php if (empty($lista)): ?>
            <tr><td colspan="6" style="text-align:center; color:var(--gris-medio); padding:2rem">Sin resultados.</td></tr>
          <?php else: foreach ($lista as $p): ?>
            <tr>
              <td><strong>#<?= $p['id'] ?></strong></td>
              <td>
                <?= e($p['unom']) ?>
                <br><small style="color:var(--gris-medio)"><?= e($p['uemail']) ?></small>
              </td>
              <td style="font-size:.85rem"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
              <td style="font-weight:700; color:var(--naranja)"><?= number_format($p['total'],2,',','.') ?> €</td>
              <td><span class="estado-badge estado-<?= e($p['estado']) ?>"><?= e(ucfirst($p['estado'])) ?></span></td>
              <td>
                <a href="<?= BASE_URL ?>/admin/presupuesto-detalle.php?id=<?= $p['id'] ?>"
                   class="btn btn-outline btn-sm">Ver →</a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
