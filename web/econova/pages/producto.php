<?php
// ============================================================
// pages/producto.php — Ficha de producto
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';

$id = sanitize_int($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . BASE_URL . '/pages/catalogo.php'); exit; }

$stmt = db()->prepare(
    "SELECT p.*, c.nombre AS cat_nombre, c.slug AS cat_slug
     FROM productos p JOIN categorias c ON p.categoria_id = c.id
     WHERE p.id = ? AND p.activo = 1"
);
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); die('Producto no encontrado.'); }

$specs  = json_decode($p['especificaciones'] ?? '{}', true);
$ahorro = $p['precio_original']
  ? round((($p['precio_original'] - $p['precio']) / $p['precio_original']) * 100) : 0;

// Favorito?
$es_fav = false;
if (usuario_logueado()) {
    $fav = db()->prepare('SELECT 1 FROM favoritos WHERE usuario_id=? AND producto_id=?');
    $fav->execute([$_SESSION['usuario_id'], $id]);
    $es_fav = (bool)$fav->fetchColumn();
}

// Mensaje tras acción
$msg = $_GET['msg'] ?? '';

// Productos relacionados
$rel = db()->prepare(
    "SELECT p.*, c.slug AS cat_slug FROM productos p JOIN categorias c ON p.categoria_id=c.id
     WHERE p.categoria_id=? AND p.id != ? AND p.activo=1 ORDER BY RAND() LIMIT 4"
);
$rel->execute([$p['categoria_id'], $id]);
$relacionados = $rel->fetchAll();

$page_title = $p['nombre'];
require_once __DIR__ . '/../includes/header.php';
?>

<script>
  const BASE_URL  = '<?= BASE_URL ?>';
  const CSRF_TOKEN = '<?= csrf_token() ?>';
</script>

<div class="container" style="padding-bottom:5rem">

  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="<?= BASE_URL ?>/index.php">Inicio</a>
    <span class="sep">/</span>
    <a href="<?= BASE_URL ?>/pages/catalogo.php">Catálogo</a>
    <span class="sep">/</span>
    <a href="<?= BASE_URL ?>/pages/catalogo.php?cat=<?= e($p['cat_slug']) ?>"><?= e($p['cat_nombre']) ?></a>
    <span class="sep">/</span>
    <span><?= e($p['nombre']) ?></span>
  </div>

  <?php if ($msg === 'carrito_ok'): ?>
    <div class="form-success"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#22C55E" stroke-width="2.5" style="vertical-align:middle;margin-right:4px"><polyline points="20 6 9 17 4 12"/></svg> Producto añadido al carrito correctamente.</div>
  <?php endif; ?>

  <!-- Ficha principal -->
  <div class="ficha-layout">

    <!-- Imagen -->
    <div>
      <div class="ficha-img-main">
        <img src="<?= ['ordenadores'=> BASE_URL.'/assets/img/desktop.svg','portatiles'=>BASE_URL.'/assets/img/laptop.svg','monitores'=>BASE_URL.'/assets/img/monitor.svg','servidores'=>BASE_URL.'/assets/img/servidor.svg','accesorios'=>BASE_URL.'/assets/img/accesorios.svg'][$p['cat_slug']] ?? BASE_URL.'/assets/img/accesorios.svg' ?>" alt="<?= e($p['nombre']) ?>" style="width:100%;height:100%;object-fit:contain;padding:2rem">
      </div>
      <!-- Grado explicado -->
      <div style="margin-top:1.5rem; background:var(--gris-lite); border-radius:8px; padding:1.25rem">
        <h4 style="font-size:.8rem; text-transform:uppercase; letter-spacing:.08em; color:var(--gris-medio); margin-bottom:.75rem">Grado de reacondicionamiento</h4>
        <div style="display:flex; gap:1rem; flex-wrap:wrap">
          <?php foreach (['A'=>['color'=>'#D4F0D4','text'=>'#1A6B1A','desc'=>'Como nuevo, sin marcas visibles'],
                          'B'=>['color'=>'#FFF3D4','text'=>'#7A5A00','desc'=>'Muy buen estado, marcas mínimas'],
                          'C'=>['color'=>'#FFE5E5','text'=>'#8B1A1A','desc'=>'Funcional, marcas de uso']] as $g => $info): ?>
            <div style="flex:1; min-width:80px; background:<?= $info['color'] ?>; color:<?= $info['text'] ?>; border-radius:6px; padding:.75rem; <?= $p['grado']===$g ? 'outline:2px solid currentColor' : 'opacity:.5' ?>">
              <strong style="font-family:var(--font-display); font-size:1.1rem">Grado <?= $g ?></strong>
              <p style="font-size:.75rem; margin-top:.25rem"><?= $info['desc'] ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Info -->
    <div class="ficha-info">
      <div>
        <span class="ficha-cat"><?= e($p['cat_nombre']) ?></span>
        <h1 class="ficha-titulo" style="margin-top:.5rem"><?= e($p['nombre']) ?></h1>
      </div>

      <div class="ficha-precios">
        <span class="ficha-precio-actual"><?= number_format($p['precio'],2,',','.') ?> €</span>
        <?php if ($p['precio_original']): ?>
          <span class="precio-original" style="font-size:1.1rem"><?= number_format($p['precio_original'],2,',','.') ?> €</span>
          <span class="precio-ahorro">Ahorras <?= $ahorro ?>%</span>
        <?php endif; ?>
      </div>

      <p style="color:var(--gris-medio); line-height:1.8"><?= e($p['descripcion']) ?></p>

      <!-- Especificaciones -->
      <?php if (!empty($specs)): ?>
        <div class="ficha-specs">
          <h4>Especificaciones técnicas</h4>
          <div class="specs-table">
            <?php
            $labels = ['cpu'=>'Procesador','ram'=>'Memoria RAM','almacenamiento'=>'Almacenamiento',
                       'os'=>'Sistema operativo','pantalla'=>'Pantalla','conectores'=>'Conectores',
                       'grado'=>'Grado','resolucion'=>'Resolución','panel'=>'Tipo de panel',
                       'factor'=>'Factor de forma','potencia'=>'Potencia','conexion'=>'Conexión',
                       'microfono'=>'Micrófono','dispositivos'=>'Multi-dispositivo','incluye'=>'Incluye',
                       'certificacion'=>'Certificación','idioma'=>'Idioma'];
            foreach ($specs as $k => $v):
              if ($k === 'grado') continue; // ya se muestra arriba
            ?>
              <div class="spec-row">
                <span class="spec-key"><?= e($labels[$k] ?? ucfirst($k)) ?></span>
                <span class="spec-val"><?= e($v) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Stock -->
      <?php if ($p['stock'] > 0): ?>
        <p class="ficha-stock"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#22C55E" stroke-width="2.5" style="vertical-align:middle;margin-right:4px"><polyline points="20 6 9 17 4 12"/></svg> <?= $p['stock'] ?> unidades disponibles · Envío en 24-48h</p>
      <?php else: ?>
        <p style="color:#C0392B; font-size:.875rem">⚠ Sin stock actualmente</p>
      <?php endif; ?>

      <!-- Acciones -->
      <div class="ficha-acciones">
        <?php if ($p['stock'] > 0): ?>
          <?php if (usuario_logueado()): ?>
            <form method="POST" action="<?= BASE_URL ?>/pages/carrito-add.php" style="display:contents">
              <?= csrf_field() ?>
              <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="redirect" value="<?= BASE_URL ?>/pages/producto.php?id=<?= $p['id'] ?>&msg=carrito_ok">
              <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> Añadir al carrito
              </button>
            </form>
          <?php else: ?>
            <a href="<?= BASE_URL ?>/pages/login.php?next=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary">
              Iniciar sesión para añadir
            </a>
          <?php endif; ?>
        <?php endif; ?>

        <?php if (usuario_logueado()): ?>
          <button class="btn btn-outline btn-favorito <?= $es_fav ? 'activo' : '' ?>"
                  id="btn-fav-principal"
                  onclick="toggleFavoritoFicha(<?= $p['id'] ?>)"
                  style="display:flex; align-items:center; gap:.5rem; width:auto; height:auto; border-radius:4px; padding:.75rem 1.25rem">
            <svg viewBox="0 0 24 24" width="18" height="18"
                 fill="<?= $es_fav ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
            <span><?= $es_fav ? 'En favoritos' : 'Favoritos' ?></span>
          </button>
        <?php endif; ?>
      </div>

      <!-- Info garantía -->
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem; margin-top:.5rem">
        <div style="background:var(--gris-lite); border-radius:6px; padding:.875rem; font-size:.82rem">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg> <strong>2 años de garantía</strong><br>en hardware
        </div>
        <div style="background:var(--gris-lite); border-radius:6px; padding:.875rem; font-size:.82rem">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg> <strong>Envío 24-48h</strong><br>península
        </div>
        <div style="background:var(--gris-lite); border-radius:6px; padding:.875rem; font-size:.82rem">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#22C55E" stroke-width="2.5" style="vertical-align:middle;margin-right:4px"><polyline points="20 6 9 17 4 12"/></svg> <strong>Revisado</strong><br>por técnicos certificados
        </div>
        <div style="background:var(--gris-lite); border-radius:6px; padding:.875rem; font-size:.82rem">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><polyline points="1.5 8.5 1.5 3.5 6.5 3.5"/><path d="M1.5 3.5C3.5 6 6 8 9 9"/><polyline points="22.5 15.5 22.5 20.5 17.5 20.5"/><path d="M22.5 20.5C20.5 18 18 16 15 15"/><polyline points="6.5 20.5 1.5 20.5 1.5 15.5"/><path d="M1.5 20.5C4 18 6.5 15.5 8 12"/><polyline points="17.5 3.5 22.5 3.5 22.5 8.5"/><path d="M22.5 3.5C20 6 17.5 8.5 16 12"/></svg> <strong>Sostenible</strong><br>CO₂ evitado certificado
        </div>
      </div>
    </div>
  </div>

  <!-- Relacionados -->
  <?php if (!empty($relacionados)): ?>
    <div style="margin-top:4rem">
      <h2 style="margin-bottom:1.5rem">También te puede interesar</h2>
      <div class="productos-grid">
        <?php foreach ($relacionados as $r):
          $r_specs  = json_decode($r['especificaciones'] ?? '{}', true);
          $r_ahorro = $r['precio_original']
            ? round((($r['precio_original'] - $r['precio']) / $r['precio_original']) * 100) : 0;
          $iconos = ['ordenadores'=> BASE_URL.'/assets/img/desktop.svg','portatiles'=>BASE_URL.'/assets/img/laptop.svg','monitores'=>BASE_URL.'/assets/img/monitor.svg','servidores'=>BASE_URL.'/assets/img/servidor.svg','accesorios'=>BASE_URL.'/assets/img/accesorios.svg'];
        ?>
          <div class="producto-card">
            <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $r['id'] ?>">
              <div class="producto-img">
                <div class="producto-img-placeholder"><img src="<?= isset($iconos[$p['cat_slug']]) ? $iconos[$p['cat_slug']] : (isset($iconos[$r['cat_slug']]) ? $iconos[$r['cat_slug']] : BASE_URL.'/assets/img/accesorios.svg') ?>" alt="" style="width:100%;height:100%;object-fit:contain;padding:1rem"></div>
                <div class="producto-badges">
                  <span class="badge badge-grado-<?= strtolower($r['grado']) ?>">Grado <?= e($r['grado']) ?></span>
                </div>
              </div>
            </a>
            <div class="producto-body">
              <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $r['id'] ?>">
                <h3 class="producto-nombre"><?= e($r['nombre']) ?></h3>
              </a>
              <div class="producto-precios">
                <span class="precio-actual"><?= number_format($r['precio'],0,',','.') ?> €</span>
                <?php if ($r_ahorro): ?><span class="precio-ahorro">-<?= $r_ahorro ?>%</span><?php endif; ?>
              </div>
            </div>
            <div class="producto-footer">
              <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $r['id'] ?>" class="btn btn-primary btn-sm" style="flex:1">Ver</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

</div>

<script>
function toggleFavoritoFicha(id) {
  const btn = document.getElementById('btn-fav-principal');
  fetch(BASE_URL + '/pages/favorito-toggle.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'producto_id=' + id + '&csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      btn.classList.toggle('activo', d.favorito);
      btn.querySelector('span').textContent = d.favorito ? 'En favoritos' : 'Favoritos';
      btn.querySelector('svg').setAttribute('fill', d.favorito ? 'currentColor' : 'none');
    } else if (d.redirect) window.location.href = d.redirect;
  });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
