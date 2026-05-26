<?php
// ============================================================
// index.php — Página de inicio EcoNova
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';

$page_title = 'Tecnología con segunda vida';

// Últimos productos destacados (8)
$stmt = db()->query(
  "SELECT p.*, c.nombre AS cat_nombre, c.slug AS cat_slug
   FROM productos p
   JOIN categorias c ON p.categoria_id = c.id
   WHERE p.activo = 1
   ORDER BY p.created_at DESC
   LIMIT 8"
);
$productos_home = $stmt->fetchAll();

// Categorías
$categorias = db()->query("SELECT * FROM categorias ORDER BY id")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Mobile nav -->
<nav class="mobile-nav" id="mobile-nav">
  <a href="<?= BASE_URL ?>/index.php">Inicio</a>
  <a href="<?= BASE_URL ?>/pages/catalogo.php">Catálogo</a>
  <a href="<?= BASE_URL ?>/pages/sobre-nosotros.php">Nosotros</a>
  <a href="<?= BASE_URL ?>/pages/contacto.php">Contacto</a>
</nav>

<script>
  const BASE_URL  = '<?= BASE_URL ?>';
  const CSRF_TOKEN = '<?= csrf_token() ?>';
</script>

<!-- ── HERO ─────────────────────────────────────────────── -->
<section style="padding:0">
  <div class="container">
    <div class="hero">
      <div class="hero-content fade-in">
        <span class="hero-label">♻ Economía circular en tecnología</span>
        <h1>
          Equipos que<br>
          <em>merecen</em><br>
          segunda vida.
        </h1>
        <p class="hero-sub">
          Equipos de empresa reacondicionados, revisados y garantizados.
          Hasta un 70% más baratos. Cero compromiso con el rendimiento.
        </p>
        <div class="hero-actions">
          <a href="<?= BASE_URL ?>/pages/catalogo.php" class="btn btn-primary">Ver catálogo</a>
          <a href="<?= BASE_URL ?>/pages/sobre-nosotros.php" class="btn btn-outline">Cómo funciona</a>
        </div>
        <div class="hero-stats">
          <div>
            <span class="hero-stat-num">+500</span>
            <span class="hero-stat-label">Equipos revisados</span>
          </div>
          <div>
            <span class="hero-stat-num">2 años</span>
            <span class="hero-stat-label">Garantía incluida</span>
          </div>
          <div>
            <span class="hero-stat-num">70%</span>
            <span class="hero-stat-label">Ahorro vs. nuevo</span>
          </div>
        </div>
      </div>

      <div class="hero-visual">
        <div class="hero-grid-products">
          <div class="hero-product-card">
            <span class="grado-badge">Grado A</span>
            <div style="font-size:2.5rem; margin: .5rem 0">💻</div>
            <div style="font-family:var(--font-display); font-weight:700; font-size:.9rem">ThinkPad T490</div>
            <div style="color:var(--naranja); font-weight:800; margin-top:.25rem">369 €</div>
            <div style="font-size:.75rem; color:#999; text-decoration:line-through">1.300 €</div>
          </div>
          <div class="hero-product-card">
            <span class="grado-badge">Grado A</span>
            <div style="font-size:2.5rem; margin: .5rem 0">🖥</div>
            <div style="font-family:var(--font-display); font-weight:700; font-size:.9rem">Dell OptiPlex</div>
            <div style="color:var(--naranja); font-weight:800; margin-top:.25rem">249 €</div>
            <div style="font-size:.75rem; color:#999; text-decoration:line-through">900 €</div>
          </div>
          <div class="hero-product-card">
            <span class="grado-badge">Grado A</span>
            <div style="font-size:2.5rem; margin: .5rem 0">🖱</div>
            <div style="font-family:var(--font-display); font-weight:700; font-size:.9rem">USB-C Dock Gen2</div>
            <div style="color:var(--naranja); font-weight:800; margin-top:.25rem">89 €</div>
            <div style="font-size:.75rem; color:#999; text-decoration:line-through">250 €</div>
          </div>
          <div class="hero-product-card">
            <span class="grado-badge">Grado A</span>
            <div style="font-size:2.5rem; margin: .5rem 0">📺</div>
            <div style="font-family:var(--font-display); font-weight:700; font-size:.9rem">Dell 27" QHD</div>
            <div style="color:var(--naranja); font-weight:800; margin-top:.25rem">189 €</div>
            <div style="font-size:.75rem; color:#999; text-decoration:line-through">550 €</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CATEGORÍAS ─────────────────────────────────────────── -->
<section style="background:var(--blanco); padding:3rem 0">
  <div class="container">
    <span class="section-number">01 — Categorías</span>
    <h2 style="margin-bottom:2rem">Encuentra lo que necesitas</h2>
    <div class="categorias-grid">
      <?php
      $iconos = ['ordenadores'=>'🖥','portatiles'=>'💻','monitores'=>'📺','servidores'=>'🗄','accesorios'=>'🖱'];
      foreach ($categorias as $cat): ?>
        <a href="<?= BASE_URL ?>/pages/catalogo.php?cat=<?= e($cat['slug']) ?>" class="categoria-card">
          <span class="categoria-icon"><?= $iconos[$cat['slug']] ?? '📦' ?></span>
          <span class="categoria-nombre"><?= e($cat['nombre']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── PRODUCTOS DESTACADOS ───────────────────────────────── -->
<section>
  <div class="container">
    <span class="section-number">02 — Catálogo</span>
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; flex-wrap:wrap; gap:1rem">
      <h2>Últimas incorporaciones</h2>
      <a href="<?= BASE_URL ?>/pages/catalogo.php" class="btn btn-ghost">Ver todos →</a>
    </div>

    <div class="productos-grid">
      <?php foreach ($productos_home as $p):
        $specs = json_decode($p['especificaciones'] ?? '{}', true);
        $ahorro = $p['precio_original']
          ? round((($p['precio_original'] - $p['precio']) / $p['precio_original']) * 100)
          : 0;
      ?>
        <div class="producto-card">
          <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $p['id'] ?>">
            <div class="producto-img">
              <div class="producto-img-placeholder">
                <?= ['ordenadores'=>'🖥','portatiles'=>'💻','monitores'=>'📺','servidores'=>'🗄','accesorios'=>'🖱'][$p['cat_slug']] ?? '📦' ?>
              </div>
              <div class="producto-badges">
                <span class="badge badge-grado-<?= strtolower($p['grado']) ?>">Grado <?= e($p['grado']) ?></span>
                <?php if ($ahorro >= 60): ?><span class="badge badge-nuevo"><?= $ahorro ?>% dto</span><?php endif; ?>
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
              </div>
            <?php endif; ?>
            <div class="producto-precios">
              <span class="precio-actual"><?= number_format($p['precio'], 0, ',', '.') ?> €</span>
              <?php if ($p['precio_original']): ?>
                <span class="precio-original"><?= number_format($p['precio_original'], 0, ',', '.') ?> €</span>
                <span class="precio-ahorro">-<?= $ahorro ?>%</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="producto-footer">
            <a href="<?= BASE_URL ?>/pages/producto.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm" style="flex:1">
              Ver detalles
            </a>
            <?php if (usuario_logueado()): ?>
              <button class="btn-favorito" onclick="toggleFavorito(<?= $p['id'] ?>, this)" title="Añadir a favoritos">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              </button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── SOSTENIBILIDAD ─────────────────────────────────────── -->
<section class="sostenibilidad-section">
  <div class="container">
    <div class="sostenibilidad-grid">
      <div>
        <span class="section-number">03 — Impacto</span>
        <h2 style="color:var(--blanco); margin-bottom:1.5rem">
          Tecnología circular.<br>Impacto real.
        </h2>
        <p style="color:#AAAAAA; font-weight:300; max-width:400px; line-height:1.8">
          Cada equipo reacondicionado evita entre 150 y 300 kg de CO₂.
          En EcoNova apostamos por alargar la vida útil de la tecnología
          porque el mejor residuo es el que no se genera.
        </p>
        <a href="<?= BASE_URL ?>/pages/sobre-nosotros.php" class="btn btn-primary mt-4">Nuestra misión</a>
      </div>
      <div class="sostenibilidad-items">
        <div class="sostenibilidad-item">
          <div class="sostenibilidad-item-icon">♻</div>
          <div>
            <h4>Economía circular</h4>
            <p>Los equipos provienen de renovaciones de flotas corporativas y organismos públicos. Revisados, actualizados y listos.</p>
          </div>
        </div>
        <div class="sostenibilidad-item">
          <div class="sostenibilidad-item-icon">🌱</div>
          <div>
            <h4>Cero residuos innecesarios</h4>
            <p>Un portátil reacondicionado requiere un 80% menos de energía de fabricación que uno nuevo equivalente.</p>
          </div>
        </div>
        <div class="sostenibilidad-item">
          <div class="sostenibilidad-item-icon">✅</div>
          <div>
            <h4>Garantía de 2 años</h4>
            <p>Todo equipo pasa por un proceso de diagnóstico, sustitución de componentes y test de rendimiento antes de la venta.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
