<?php
// pages/favoritos.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion(); require_login();

$stmt = db()->prepare(
    "SELECT p.*, c.nombre AS cat_nombre, c.slug AS cat_slug
     FROM favoritos f JOIN productos p ON f.producto_id=p.id JOIN categorias c ON p.categoria_id=c.id
     WHERE f.usuario_id=? AND p.activo=1 ORDER BY f.created_at DESC"
);
$stmt->execute([$_SESSION['usuario_id']]);
$favoritos = $stmt->fetchAll();

$page_title = 'Mis favoritos';
require_once __DIR__ . '/../includes/header.php';
?>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrf_token() ?>';</script>

<div class="container" style="padding:2rem 1.5rem 5rem">
  <h1 style="margin-bottom:2rem">Mis favoritos</h1>

  <?php if (empty($favoritos)): ?>
    <div class="empty-state">
      <div class="empty-state-icon"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div>
      <h3>Sin favoritos aún</h3>
      <p>Guarda los equipos que te interesan para encontrarlos fácilmente.</p>
      <a href="<?= BASE_URL ?>/pages/catalogo.php" class="btn btn-primary mt-4">Explorar catálogo</a>
    </div>
  <?php else: ?>
    <div class="productos-grid">
      <?php
      $iconos = ['ordenadores'=> BASE_URL.'/assets/img/desktop.svg','portatiles'=>BASE_URL.'/assets/img/laptop.svg','monitores'=>BASE_URL.'/assets/img/monitor.svg','servidores'=>BASE_URL.'/assets/img/servidor.svg','accesorios'=>BASE_URL.'/assets/img/accesorios.svg'];
      foreach ($favoritos as $p):
        $ahorro = $p['precio_original']
          ? round((($p['precio_original']-$p['precio'])/$p['precio_original'])*100) : 0;
      ?>
        <div class="producto-card">
          <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $p['id'] ?>">
            <div class="producto-img">
              <div class="producto-img-placeholder"><img src="<?= $iconos[$p['cat_slug']] ?? BASE_URL.'/assets/img/accesorios.svg' ?>" alt="<?= e($p['nombre']) ?>" style="width:100%;height:100%;object-fit:contain;padding:1rem"></div>
              <div class="producto-badges">
                <span class="badge badge-grado-<?= strtolower($p['grado']) ?>">Grado <?= e($p['grado']) ?></span>
              </div>
            </div>
          </a>
          <div class="producto-body">
            <span class="producto-categoria"><?= e($p['cat_nombre']) ?></span>
            <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $p['id'] ?>">
              <h3 class="producto-nombre"><?= e($p['nombre']) ?></h3>
            </a>
            <div class="producto-precios">
              <span class="precio-actual"><?= number_format($p['precio'],0,',','.') ?> €</span>
              <?php if ($ahorro): ?><span class="precio-ahorro">-<?= $ahorro ?>%</span><?php endif; ?>
            </div>
          </div>
          <div class="producto-footer">
            <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm" style="flex:1">Ver</a>
            <button class="btn-favorito activo" onclick="toggleFavorito(<?= $p['id'] ?>, this)">
              <svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
