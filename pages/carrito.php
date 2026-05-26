<?php
// pages/carrito.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion();
require_login();

$uid = (int)$_SESSION['usuario_id'];

// Eliminar item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    csrf_verify();
    $del_id = sanitize_int($_POST['eliminar']);
    db()->prepare('DELETE FROM carrito WHERE usuario_id=? AND producto_id=?')->execute([$uid, $del_id]);
    header('Location: ' . BASE_URL . '/pages/carrito.php'); exit;
}

// Actualizar cantidad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    csrf_verify();
    $upd_id  = sanitize_int($_POST['producto_id']);
    $upd_qty = max(1, sanitize_int($_POST['cantidad']));
    db()->prepare('UPDATE carrito SET cantidad=? WHERE usuario_id=? AND producto_id=?')
        ->execute([$upd_qty, $uid, $upd_id]);
    header('Location: ' . BASE_URL . '/pages/carrito.php'); exit;
}

// Solicitar presupuesto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar_presupuesto'])) {
    csrf_verify();
    $notas = trim(strip_tags($_POST['notas'] ?? ''));

    // Leer carrito
    $items_stmt = db()->prepare(
        'SELECT c.cantidad, p.id, p.precio FROM carrito c JOIN productos p ON c.producto_id=p.id WHERE c.usuario_id=?'
    );
    $items_stmt->execute([$uid]);
    $items = $items_stmt->fetchAll();

    if (!empty($items)) {
        $total = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $items));

        db()->beginTransaction();
        $ins_pres = db()->prepare('INSERT INTO presupuestos (usuario_id, notas, total) VALUES (?,?,?)');
        $ins_pres->execute([$uid, $notas, $total]);
        $pres_id = (int)db()->lastInsertId();

        $ins_item = db()->prepare('INSERT INTO presupuesto_items (presupuesto_id, producto_id, cantidad, precio_unitario) VALUES (?,?,?,?)');
        foreach ($items as $item) {
            $ins_item->execute([$pres_id, $item['id'], $item['cantidad'], $item['precio']]);
        }

        // Vaciar carrito
        db()->prepare('DELETE FROM carrito WHERE usuario_id=?')->execute([$uid]);
        db()->commit();

        audit('presupuesto_solicitud', 'presupuestos', $pres_id);
        header('Location: ' . BASE_URL . '/pages/mis-presupuestos.php?nuevo=1'); exit;
    }
}

// Cargar carrito
$stmt = db()->prepare(
    "SELECT c.cantidad, p.id, p.nombre, p.precio, p.precio_original, p.grado, p.stock,
            cat.slug AS cat_slug
     FROM carrito c
     JOIN productos p ON c.producto_id = p.id
     JOIN categorias cat ON p.categoria_id = cat.id
     WHERE c.usuario_id = ?"
);
$stmt->execute([$uid]);
$items = $stmt->fetchAll();

$total = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $items));

$page_title = 'Carrito';
require_once __DIR__ . '/../includes/header.php';
?>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrf_token() ?>';</script>

<div class="container" style="padding:2rem 1.5rem 5rem">
  <h1 style="margin-bottom:2rem">Tu carrito</h1>

  <?php if (empty($items)): ?>
    <div class="empty-state">
      <div class="empty-state-icon">🛒</div>
      <h3>Tu carrito está vacío</h3>
      <p>Explora nuestro catálogo y añade equipos.</p>
      <a href="<?= BASE_URL ?>/pages/catalogo.php" class="btn btn-primary mt-4">Ver catálogo</a>
    </div>
  <?php else: ?>

    <div class="carrito-layout">
      <!-- Items -->
      <div>
        <?php
        $iconos = ['ordenadores'=>'<img src="<?= BASE_URL ?>/assets/img/desktop.svg" alt="Ordenador" style="width:48px;height:48px;object-fit:contain">','portatiles'=>'<img src="<?= BASE_URL ?>/assets/img/laptop.svg" alt="Portátil" style="width:48px;height:48px;object-fit:contain">','monitores'=>'<img src="<?= BASE_URL ?>/assets/img/monitor.svg" alt="Monitor" style="width:48px;height:48px;object-fit:contain">','servidores'=>'<img src="<?= BASE_URL ?>/assets/img/servidor.svg" alt="Servidor" style="width:48px;height:48px;object-fit:contain">','accesorios'=>'<img src="<?= BASE_URL ?>/assets/img/accesorios.svg" alt="Accesorios" style="width:48px;height:48px;object-fit:contain">'];
        foreach ($items as $item): ?>
          <div class="carrito-item">
            <div class="carrito-item-img"><?= $iconos[$item['cat_slug']] ?? '📦' ?></div>
            <div style="flex:1">
              <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $item['id'] ?>" style="font-family:var(--font-display); font-weight:700">
                <?= e($item['nombre']) ?>
              </a>
              <p style="font-size:.8rem; color:var(--gris-medio); margin:.25rem 0">
                Grado <?= e($item['grado']) ?> · <?= number_format($item['precio'],2,',','.') ?> € / ud
              </p>
              <form method="POST" style="display:flex; gap:.5rem; align-items:center; margin-top:.5rem">
                <?= csrf_field() ?>
                <input type="hidden" name="producto_id" value="<?= $item['id'] ?>">
                <input type="number" name="cantidad" value="<?= $item['cantidad'] ?>"
                       min="1" max="<?= $item['stock'] ?>"
                       style="width:60px; padding:.3rem .5rem; border:1px solid var(--borde); border-radius:4px; font-family:var(--font-body)">
                <button name="actualizar" value="1" class="btn btn-outline btn-sm">Actualizar</button>
              </form>
            </div>
            <div style="text-align:right">
              <div style="font-family:var(--font-display); font-weight:800; color:var(--naranja); font-size:1.1rem">
                <?= number_format($item['precio'] * $item['cantidad'], 2, ',', '.') ?> €
              </div>
              <form method="POST" style="margin-top:.5rem">
                <?= csrf_field() ?>
                <button name="eliminar" value="<?= $item['id'] ?>"
                        style="background:none; border:none; cursor:pointer; color:var(--gris-medio); font-size:.8rem"
                        onclick="return confirm('¿Quitar este producto del carrito?')">
                  ✕ Eliminar
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Resumen y solicitud de presupuesto -->
      <div>
        <div class="carrito-resumen">
          <h3 style="margin-bottom:1rem">Resumen</h3>
          <?php foreach ($items as $item): ?>
            <div class="resumen-row">
              <span><?= e($item['nombre']) ?> ×<?= $item['cantidad'] ?></span>
              <span><?= number_format($item['precio'] * $item['cantidad'], 2, ',', '.') ?> €</span>
            </div>
          <?php endforeach; ?>
          <div class="resumen-row total">
            <span>Total estimado</span>
            <span><?= number_format($total, 2, ',', '.') ?> €</span>
          </div>
          <div class="resumen-nota">
            ℹ Este carrito se convierte en una <strong>solicitud de presupuesto</strong>.
            Nuestro equipo lo revisará y te contactará en 24-48h.
          </div>

          <form method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
              <label>Notas para el presupuesto (opcional)</label>
              <textarea name="notas" rows="3" placeholder="Necesidades especiales, cantidad, empresa..."></textarea>
            </div>
            <button type="submit" name="solicitar_presupuesto" value="1" class="btn btn-primary" style="width:100%">
              📋 Solicitar presupuesto
            </button>
          </form>
        </div>
      </div>
    </div>

  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
