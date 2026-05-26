<?php
// ============================================================
// pages/catalogo.php — Catálogo con filtros y paginación
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';

$page_title = 'Catálogo';

// ── Filtros desde GET (sanitizados) ──────────────────────────
$cat      = $_GET['cat']      ?? '';
$grado    = $_GET['grado']    ?? [];
$precio_min = isset($_GET['precio_min']) ? max(0, (int)$_GET['precio_min']) : 0;
$precio_max = isset($_GET['precio_max']) ? min(9999, (int)$_GET['precio_max']) : 9999;
$busqueda = isset($_GET['q']) ? trim(strip_tags($_GET['q'])) : '';
$orden    = $_GET['orden']    ?? 'reciente';
$pagina   = max(1, (int)($_GET['p'] ?? 1));

if (!is_array($grado)) $grado = [];
$grado_validos = ['A','B','C'];
$grado = array_filter($grado, fn($g) => in_array($g, $grado_validos));

// ── Categorías para filtro lateral ───────────────────────────
$categorias = db()->query("SELECT * FROM categorias ORDER BY id")->fetchAll();

// ── Query dinámica con prepared statements ────────────────────
$where  = ['p.activo = 1'];
$params = [];

if ($cat !== '') {
    $where[]  = 'c.slug = ?';
    $params[] = $cat;
}
if (!empty($grado)) {
    $in = implode(',', array_fill(0, count($grado), '?'));
    $where[] = "p.grado IN ($in)";
    $params  = array_merge($params, array_values($grado));
}
if ($precio_min > 0) {
    $where[]  = 'p.precio >= ?';
    $params[] = $precio_min;
}
if ($precio_max < 9999) {
    $where[]  = 'p.precio <= ?';
    $params[] = $precio_max;
}
if ($busqueda !== '') {
    $where[]  = '(p.nombre LIKE ? OR p.descripcion LIKE ?)';
    $like     = '%' . $busqueda . '%';
    $params[] = $like;
    $params[] = $like;
}

$where_sql = implode(' AND ', $where);

$orden_sql = match($orden) {
    'precio_asc'  => 'p.precio ASC',
    'precio_desc' => 'p.precio DESC',
    'ahorro'      => '((p.precio_original - p.precio)/p.precio_original) DESC',
    default       => 'p.created_at DESC',
};

// Total para paginación
$count_stmt = db()->prepare(
    "SELECT COUNT(*) FROM productos p JOIN categorias c ON p.categoria_id = c.id WHERE $where_sql"
);
$count_stmt->execute($params);
$total = (int) $count_stmt->fetchColumn();
$total_paginas = max(1, ceil($total / PRODUCTOS_POR_PAGINA));
$pagina = min($pagina, $total_paginas);
$offset = ($pagina - 1) * PRODUCTOS_POR_PAGINA;

// Productos paginados
$stmt = db()->prepare(
    "SELECT p.*, c.nombre AS cat_nombre, c.slug AS cat_slug
     FROM productos p
     JOIN categorias c ON p.categoria_id = c.id
     WHERE $where_sql
     ORDER BY $orden_sql
     LIMIT ? OFFSET ?"
);
$stmt->execute(array_merge($params, [PRODUCTOS_POR_PAGINA, $offset]));
$productos = $stmt->fetchAll();

// Favoritos del usuario para marcar corazones
$favoritos_ids = [];
if (usuario_logueado()) {
    $fav = db()->prepare('SELECT producto_id FROM favoritos WHERE usuario_id = ?');
    $fav->execute([$_SESSION['usuario_id']]);
    $favoritos_ids = $fav->fetchAll(PDO::FETCH_COLUMN);
}

// ── Helper URL de paginación ─────────────────────────────────
function pagina_url(int $p): string {
    $params = $_GET;
    $params['p'] = $p;
    return '?' . http_build_query($params);
}

require_once __DIR__ . '/../includes/header.php';
?>

<script>
  const BASE_URL  = '<?= BASE_URL ?>';
  const CSRF_TOKEN = '<?= csrf_token() ?>';
</script>

<div class="container" style="padding-top:2rem; padding-bottom:4rem">

  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="<?= BASE_URL ?>/index.php">Inicio</a>
    <span class="sep">/</span>
    <span>Catálogo</span>
    <?php if ($cat): ?>
      <span class="sep">/</span>
      <span><?= e(array_column($categorias, 'nombre', 'slug')[$cat] ?? $cat) ?></span>
    <?php endif; ?>
  </div>

  <div class="catalogo-layout" style="margin-top:1.5rem">

    <!-- ── FILTROS ──────────────────────────────────────────── -->
    <aside>
      <form id="filtros-form" method="GET" action="">
        <div class="filtros-panel">
          <h3>Filtros</h3>

          <!-- Búsqueda -->
          <div class="filtro-grupo">
            <h4>Buscar</h4>
            <input type="search" name="q"
                   value="<?= e($busqueda) ?>"
                   placeholder="Nombre, modelo..."
                   style="width:100%; padding:.5rem .75rem; border:1px solid var(--borde); border-radius:4px; font-family:var(--font-body)">
          </div>

          <!-- Categoría -->
          <div class="filtro-grupo">
            <h4>Categoría</h4>
            <?php foreach ($categorias as $c): ?>
              <label>
                <input type="radio" name="cat"
                       value="<?= e($c['slug']) ?>"
                       <?= $cat === $c['slug'] ? 'checked' : '' ?>>
                <?= e($c['nombre']) ?>
              </label>
            <?php endforeach; ?>
            <?php if ($cat): ?>
              <label>
                <input type="radio" name="cat" value="" <?= $cat === '' ? 'checked' : '' ?>>
                <em>Todas las categorías</em>
              </label>
            <?php endif; ?>
          </div>

          <!-- Grado -->
          <div class="filtro-grupo">
            <h4>Grado de reacondicionamiento</h4>
            <?php foreach (['A'=>'A — Como nuevo','B'=>'B — Muy buen estado','C'=>'C — Funcional'] as $g => $label): ?>
              <label>
                <input type="checkbox" name="grado[]" value="<?= $g ?>"
                       <?= in_array($g, $grado) ? 'checked' : '' ?>>
                <?= $label ?>
              </label>
            <?php endforeach; ?>
          </div>

          <!-- Precio -->
          <div class="filtro-grupo">
            <h4>Rango de precio</h4>
            <div class="filtro-rango">
              <input type="number" name="precio_min" min="0" max="9999"
                     value="<?= $precio_min ?: '' ?>" placeholder="Min €">
              <span>—</span>
              <input type="number" name="precio_max" min="0" max="9999"
                     value="<?= $precio_max < 9999 ? $precio_max : '' ?>" placeholder="Max €">
            </div>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%">Aplicar</button>
          <a href="<?= BASE_URL ?>/pages/catalogo.php" class="btn btn-ghost btn-sm mt-2" style="width:100%; text-align:center">
            Limpiar filtros
          </a>
        </div>
      </form>
    </aside>

    <!-- ── PRODUCTOS ─────────────────────────────────────────── -->
    <div>
      <div class="catalogo-topbar">
        <p class="catalogo-total">
          <?= $total ?> equipos encontrados
          <?php if ($busqueda): ?> para "<strong><?= e($busqueda) ?></strong>"<?php endif; ?>
        </p>
        <select name="orden" class="select-orden" onchange="aplicarOrden(this.value)">
          <option value="reciente"    <?= $orden==='reciente'    ? 'selected' : '' ?>>Más recientes</option>
          <option value="precio_asc"  <?= $orden==='precio_asc'  ? 'selected' : '' ?>>Precio: menor a mayor</option>
          <option value="precio_desc" <?= $orden==='precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
          <option value="ahorro"      <?= $orden==='ahorro'      ? 'selected' : '' ?>>Mayor descuento</option>
        </select>
      </div>

      <?php if (empty($productos)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">🔍</div>
          <h3>No encontramos equipos</h3>
          <p>Prueba a cambiar los filtros o <a href="<?= BASE_URL ?>/pages/catalogo.php" style="color:var(--naranja)">ver todo el catálogo</a>.</p>
        </div>
      <?php else: ?>
        <div class="productos-grid">
          <?php foreach ($productos as $p):
            $specs  = json_decode($p['especificaciones'] ?? '{}', true);
            $ahorro = $p['precio_original']
              ? round((($p['precio_original'] - $p['precio']) / $p['precio_original']) * 100)
              : 0;
            $es_fav = in_array($p['id'], $favoritos_ids);
            $iconos = ['ordenadores'=>'<img src="<?= BASE_URL ?>/assets/img/desktop.svg" alt="Ordenador" style="width:48px;height:48px;object-fit:contain">','portatiles'=>'<img src="<?= BASE_URL ?>/assets/img/laptop.svg" alt="Portátil" style="width:48px;height:48px;object-fit:contain">','monitores'=>'<img src="<?= BASE_URL ?>/assets/img/monitor.svg" alt="Monitor" style="width:48px;height:48px;object-fit:contain">','servidores'=>'<img src="<?= BASE_URL ?>/assets/img/servidor.svg" alt="Servidor" style="width:48px;height:48px;object-fit:contain">','accesorios'=>'<img src="<?= BASE_URL ?>/assets/img/accesorios.svg" alt="Accesorios" style="width:48px;height:48px;object-fit:contain">'];
          ?>
            <div class="producto-card">
              <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $p['id'] ?>">
                <div class="producto-img">
                  <div class="producto-img-placeholder"><?= $iconos[$p['cat_slug']] ?? '📦' ?></div>
                  <div class="producto-badges">
                    <span class="badge badge-grado-<?= strtolower($p['grado']) ?>">Grado <?= e($p['grado']) ?></span>
                    <?php if ($ahorro >= 55): ?><span class="badge badge-nuevo">-<?= $ahorro ?>%</span><?php endif; ?>
                  </div>
                </div>
              </a>
              <div class="producto-body">
                <span class="producto-categoria"><?= e($p['cat_nombre']) ?></span>
                <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $p['id'] ?>">
                  <h3 class="producto-nombre"><?= e($p['nombre']) ?></h3>
                </a>
                <?php if (!empty($specs)): ?>
                  <div class="producto-specs">
                    <?php if (!empty($specs['cpu'])): ?><span>⚡ <?= e($specs['cpu']) ?></span><?php endif; ?>
                    <?php if (!empty($specs['ram'])): ?><span>💾 <?= e($specs['ram']) ?></span><?php endif; ?>
                    <?php if (!empty($specs['almacenamiento'])): ?><span>💿 <?= e($specs['almacenamiento']) ?></span><?php endif; ?>
                    <?php if (!empty($specs['pantalla'])): ?><span>🖥 <?= e($specs['pantalla']) ?></span><?php endif; ?>
                  </div>
                <?php endif; ?>
                <div class="producto-precios">
                  <span class="precio-actual"><?= number_format($p['precio'],0,',','.') ?> €</span>
                  <?php if ($p['precio_original']): ?>
                    <span class="precio-original"><?= number_format($p['precio_original'],0,',','.') ?> €</span>
                    <span class="precio-ahorro">-<?= $ahorro ?>%</span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="producto-footer">
                <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $p['id'] ?>"
                   class="btn btn-primary btn-sm" style="flex:1">Ver detalles</a>
                <?php if (usuario_logueado()): ?>
                  <button class="btn-favorito <?= $es_fav ? 'activo' : '' ?>"
                          onclick="toggleFavorito(<?= $p['id'] ?>, this)"
                          title="<?= $es_fav ? 'Quitar de favoritos' : 'Añadir a favoritos' ?>">
                    <svg viewBox="0 0 24 24" fill="<?= $es_fav ? 'currentColor' : 'none' ?>"
                         stroke="currentColor" stroke-width="2">
                      <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                  </button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Paginación -->
        <?php if ($total_paginas > 1): ?>
          <div class="paginacion">
            <?php if ($pagina > 1): ?>
              <a href="<?= pagina_url($pagina-1) ?>">‹</a>
            <?php endif; ?>
            <?php for ($i = max(1,$pagina-2); $i <= min($total_paginas,$pagina+2); $i++): ?>
              <?php if ($i === $pagina): ?>
                <span class="activa"><?= $i ?></span>
              <?php else: ?>
                <a href="<?= pagina_url($i) ?>"><?= $i ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <?php if ($pagina < $total_paginas): ?>
              <a href="<?= pagina_url($pagina+1) ?>">›</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
function aplicarOrden(valor) {
  const url = new URL(window.location.href);
  url.searchParams.set('orden', valor);
  url.searchParams.delete('p');
  window.location.href = url.toString();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
