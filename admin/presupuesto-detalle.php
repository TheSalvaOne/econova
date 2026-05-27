<?php
// admin/presupuesto-detalle.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion(); require_admin();

$id = sanitize_int($_GET['id'] ?? 0);
$pres = db()->prepare("SELECT p.*, u.nombre AS unom, u.email AS uemail FROM presupuestos p JOIN usuarios u ON p.usuario_id=u.id WHERE p.id=?");
$pres->execute([$id]);
$presupuesto = $pres->fetch();
if (!$presupuesto) die('No encontrado.');

// Actualizar estado
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $nuevo_estado = $_POST['estado'] ?? '';
    $estados_validos = ['pendiente','revisando','aprobado','rechazado'];
    if (in_array($nuevo_estado, $estados_validos)) {
        db()->prepare('UPDATE presupuestos SET estado=? WHERE id=?')->execute([$nuevo_estado, $id]);
        audit('presupuesto_estado_cambio', 'presupuestos', $id);
        header('Location: ' . BASE_URL . '/admin/presupuesto-detalle.php?id=' . $id . '&updated=1'); exit;
    }
}

$items = db()->prepare("SELECT pi.*, p.nombre FROM presupuesto_items pi JOIN productos p ON pi.producto_id=p.id WHERE pi.presupuesto_id=?");
$items->execute([$id]);
$lineas = $items->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Presupuesto #<?= $id ?> — Admin</title>
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
      <a href="<?= BASE_URL ?>/admin/presupuestos.php" class="active"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg> Presupuestos</a>
      <a href="<?= BASE_URL ?>/admin/usuarios.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Usuarios</a>
      <a href="<?= BASE_URL ?>/admin/audit.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg> Audit log</a>
      <hr style="border-color:rgba(255,255,255,.1); margin:1rem 0">
      <a href="<?= BASE_URL ?>/index.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> Ver web</a>
      <a href="<?= BASE_URL ?>/pages/logout.php"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Cerrar sesión</a>
    </nav>
  </aside>
  <div class="admin-content">
    <div style="margin-bottom:1.5rem">
      <a href="<?= BASE_URL ?>/admin/presupuestos.php" style="color:var(--naranja); font-size:.875rem">← Volver</a>
    </div>

    <?php if (!empty($_GET['updated'])): ?>
      <div class="form-success">Estado actualizado correctamente.</div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 300px; gap:1.5rem; align-items:start">
      <div class="admin-card">
        <h2 style="margin-bottom:1.5rem">Presupuesto #<?= $id ?></h2>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem">
          <div>
            <p style="font-size:.75rem; text-transform:uppercase; letter-spacing:.08em; color:var(--gris-medio)">Cliente</p>
            <p style="font-weight:700"><?= e($presupuesto['unom']) ?></p>
            <p style="font-size:.875rem; color:var(--gris-medio)"><?= e($presupuesto['uemail']) ?></p>
          </div>
          <div>
            <p style="font-size:.75rem; text-transform:uppercase; letter-spacing:.08em; color:var(--gris-medio)">Fecha</p>
            <p style="font-weight:700"><?= date('d/m/Y H:i', strtotime($presupuesto['created_at'])) ?></p>
          </div>
        </div>
        <?php if ($presupuesto['notas']): ?>
          <div style="background:var(--gris-lite); border-radius:6px; padding:1rem; margin-bottom:1.5rem; border-left:3px solid var(--naranja)">
            <p style="font-size:.75rem; color:var(--gris-medio); margin-bottom:.25rem">NOTAS DEL CLIENTE</p>
            <p><?= e($presupuesto['notas']) ?></p>
          </div>
        <?php endif; ?>
        <table class="tabla-admin">
          <thead><tr><th>Producto</th><th>Cant.</th><th>Precio ud.</th><th>Subtotal</th></tr></thead>
          <tbody>
            <?php foreach ($lineas as $l): ?>
              <tr>
                <td><?= e($l['nombre']) ?></td>
                <td><?= $l['cantidad'] ?></td>
                <td><?= number_format($l['precio_unitario'],2,',','.') ?> €</td>
                <td style="font-weight:700"><?= number_format($l['precio_unitario']*$l['cantidad'],2,',','.') ?> €</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" style="text-align:right; font-weight:700; padding:.875rem 1rem">TOTAL</td>
              <td style="font-family:var(--font-display); font-size:1.2rem; font-weight:800; color:var(--naranja); padding:.875rem 1rem">
                <?= number_format($presupuesto['total'],2,',','.') ?> €
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Cambiar estado -->
      <div class="admin-card">
        <h4 style="margin-bottom:1rem">Gestionar estado</h4>
        <div style="margin-bottom:1rem">
          <span class="estado-badge estado-<?= e($presupuesto['estado']) ?>" style="font-size:.85rem">
            Estado actual: <?= e(ucfirst($presupuesto['estado'])) ?>
          </span>
        </div>
        <form method="POST">
          <?= csrf_field() ?>
          <div class="form-group">
            <label>Cambiar a</label>
            <select name="estado">
              <option value="pendiente"   <?= $presupuesto['estado']==='pendiente'   ? 'selected':'' ?>>Pendiente</option>
              <option value="revisando"   <?= $presupuesto['estado']==='revisando'   ? 'selected':'' ?>>Revisando</option>
              <option value="aprobado"    <?= $presupuesto['estado']==='aprobado'    ? 'selected':'' ?>>Aprobado</option>
              <option value="rechazado"   <?= $presupuesto['estado']==='rechazado'   ? 'selected':'' ?>>Rechazado</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%">Actualizar estado</button>
        </form>
      </div>
    </div>
  </div>
</div>
</body>
</html>
