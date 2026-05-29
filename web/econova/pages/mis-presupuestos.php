<?php
// pages/mis-presupuestos.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion(); require_login();

$uid = (int)$_SESSION['usuario_id'];
$stmt = db()->prepare(
    "SELECT * FROM presupuestos WHERE usuario_id=? ORDER BY created_at DESC"
);
$stmt->execute([$uid]);
$presupuestos = $stmt->fetchAll();

$page_title = 'Mis presupuestos';
require_once __DIR__ . '/../includes/header.php';
?>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrf_token() ?>';</script>

<div class="container" style="padding:2rem 1.5rem 5rem">
  <h1 style="margin-bottom:.5rem">Mis presupuestos</h1>
  <p style="color:var(--gris-medio); margin-bottom:2rem">Historial de solicitudes enviadas a EcoNova.</p>

  <?php if (!empty($_GET['nuevo'])): ?>
    <div class="form-success"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#22C55E" stroke-width="2.5" style="vertical-align:middle;margin-right:4px"><polyline points="20 6 9 17 4 12"/></svg> ¡Presupuesto solicitado! Te contactaremos en 24-48h.</div>
  <?php endif; ?>

  <?php if (empty($presupuestos)): ?>
    <div class="empty-state">
      <div class="empty-state-icon"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg></div>
      <h3>Sin presupuestos</h3>
      <p>Añade equipos al carrito y solicita tu primer presupuesto.</p>
      <a href="<?= BASE_URL ?>/pages/catalogo.php" class="btn btn-primary mt-4">Explorar catálogo</a>
    </div>
  <?php else: ?>
    <?php foreach ($presupuestos as $pres): ?>
      <div class="admin-card mb-4" style="margin-bottom:1.5rem">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem">
          <div>
            <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:.25rem">
              <strong style="font-family:var(--font-display)">#<?= $pres['id'] ?></strong>
              <span class="estado-badge estado-<?= e($pres['estado']) ?>"><?= e(ucfirst($pres['estado'])) ?></span>
            </div>
            <p style="font-size:.85rem; color:var(--gris-medio)"><?= date('d/m/Y H:i', strtotime($pres['created_at'])) ?></p>
            <?php if ($pres['notas']): ?>
              <p style="font-size:.875rem; margin-top:.5rem"><em>"<?= e($pres['notas']) ?>"</em></p>
            <?php endif; ?>
          </div>
          <div style="text-align:right">
            <div style="font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--naranja)">
              <?= number_format($pres['total'],2,',','.') ?> €
            </div>
            <p style="font-size:.75rem; color:var(--gris-medio)">Total estimado</p>
          </div>
        </div>

        <!-- Detalle items -->
        <?php
        $items_stmt = db()->prepare(
            "SELECT pi.cantidad, pi.precio_unitario, p.nombre
             FROM presupuesto_items pi JOIN productos p ON pi.producto_id=p.id
             WHERE pi.presupuesto_id=?"
        );
        $items_stmt->execute([$pres['id']]);
        $pit = $items_stmt->fetchAll();
        ?>
        <?php if (!empty($pit)): ?>
          <div class="divider"></div>
          <table class="tabla-admin" style="margin:0">
            <thead><tr><th>Producto</th><th>Cant.</th><th>Precio ud.</th><th>Subtotal</th></tr></thead>
            <tbody>
              <?php foreach ($pit as $pi): ?>
                <tr>
                  <td><?= e($pi['nombre']) ?></td>
                  <td><?= $pi['cantidad'] ?></td>
                  <td><?= number_format($pi['precio_unitario'],2,',','.') ?> €</td>
                  <td><?= number_format($pi['precio_unitario']*$pi['cantidad'],2,',','.') ?> €</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
