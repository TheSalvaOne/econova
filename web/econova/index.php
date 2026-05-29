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
        <span class="hero-label"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><polyline points="1.5 8.5 1.5 3.5 6.5 3.5"/><path d="M1.5 3.5C3.5 6 6 8 9 9"/><polyline points="22.5 15.5 22.5 20.5 17.5 20.5"/><path d="M22.5 20.5C20.5 18 18 16 15 15"/><polyline points="6.5 20.5 1.5 20.5 1.5 15.5"/><path d="M1.5 20.5C4 18 6.5 15.5 8 12"/><polyline points="17.5 3.5 22.5 3.5 22.5 8.5"/><path d="M22.5 3.5C20 6 17.5 8.5 16 12"/></svg> Economía circular en tecnología</span>
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
            <img src="<?= BASE_URL ?>/assets/img/laptop.svg" alt="Portátil" style="width:80px;height:80px">
            <div style="font-family:var(--font-display); font-weight:700; font-size:.9rem">ThinkPad T490</div>
            <div style="color:var(--naranja); font-weight:800; margin-top:.25rem">369 €</div>
            <div style="font-size:.75rem; color:#999; text-decoration:line-through">1.300 €</div>
          </div>
          <div class="hero-product-card">
            <span class="grado-badge">Grado A</span>
            <img src="<?= BASE_URL ?>/assets/img/desktop.svg" alt="Ordenador" style="width:80px;height:80px">
            <div style="font-family:var(--font-display); font-weight:700; font-size:.9rem">Dell OptiPlex</div>
            <div style="color:var(--naranja); font-weight:800; margin-top:.25rem">249 €</div>
            <div style="font-size:.75rem; color:#999; text-decoration:line-through">900 €</div>
          </div>
          <div class="hero-product-card">
            <span class="grado-badge">Grado A</span>
            <img src="<?= BASE_URL ?>/assets/img/accesorios.svg" alt="Accesorios" style="width:80px;height:80px">
            <div style="font-family:var(--font-display); font-weight:700; font-size:.9rem">USB-C Dock Gen2</div>
            <div style="color:var(--naranja); font-weight:800; margin-top:.25rem">89 €</div>
            <div style="font-size:.75rem; color:#999; text-decoration:line-through">250 €</div>
          </div>
          <div class="hero-product-card">
            <span class="grado-badge">Grado A</span>
            <img src="<?= BASE_URL ?>/assets/img/monitor.svg" alt="Monitor" style="width:80px;height:80px">
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
      $iconos = ['ordenadores'=> BASE_URL.'/assets/img/desktop.svg','portatiles'=>BASE_URL.'/assets/img/laptop.svg','monitores'=>BASE_URL.'/assets/img/monitor.svg','servidores'=>BASE_URL.'/assets/img/servidor.svg','accesorios'=>BASE_URL.'/assets/img/accesorios.svg'];
      foreach ($categorias as $cat): ?>
        <a href="<?= BASE_URL ?>/pages/catalogo.php?cat=<?= e($cat['slug']) ?>" class="categoria-card">
          <span class="categoria-icon"><img src="<?= $iconos[$cat['slug']] ?? BASE_URL.'/assets/img/accesorios.svg' ?>" alt="" style="width:40px;height:40px;object-fit:contain"></span>
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
              <div class="producto-img-placeholder"><img src="<?= $iconos[$p['cat_slug']] ?? BASE_URL.'/assets/img/accesorios.svg' ?>" alt="<?= e($p['nombre']) ?>" style="width:100%;height:100%;object-fit:contain;padding:1rem"></div>
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
                <?php if (!empty($specs['cpu'])): ?><span><svg viewBox="0 0 16 16" width="14" height="14" fill="#F06A00" style="vertical-align:middle;margin-right:3px"><path d="M9.5 1L3 9h5l-1.5 6L14 7H9L9.5 1z"/></svg> <?= e($specs['cpu']) ?></span><?php endif; ?>
                <?php if (!empty($specs['ram'])): ?><span><svg viewBox="0 0 16 16" width="14" height="14" fill="#F06A00" style="vertical-align:middle;margin-right:3px"><rect x="2" y="2" width="12" height="12" rx="1"/><rect x="5" y="2" width="6" height="5" fill="#fff" rx="0.5"/><rect x="4" y="9" width="8" height="4" fill="#fff" rx="0.5"/></svg> <?= e($specs['ram']) ?></span><?php endif; ?>
                <?php if (!empty($specs['almacenamiento'])): ?><span><svg viewBox="0 0 16 16" width="14" height="14" fill="#F06A00" style="vertical-align:middle;margin-right:3px"><circle cx="8" cy="8" r="6" stroke="#F06A00" stroke-width="1.5" fill="none"/><circle cx="8" cy="8" r="2"/></svg> <?= e($specs['almacenamiento']) ?></span><?php endif; ?>
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
          <div class="sostenibilidad-item-icon"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><polyline points="1.5 8.5 1.5 3.5 6.5 3.5"/><path d="M1.5 3.5C3.5 6 6 8 9 9"/><polyline points="22.5 15.5 22.5 20.5 17.5 20.5"/><path d="M22.5 20.5C20.5 18 18 16 15 15"/><polyline points="6.5 20.5 1.5 20.5 1.5 15.5"/><path d="M1.5 20.5C4 18 6.5 15.5 8 12"/><polyline points="17.5 3.5 22.5 3.5 22.5 8.5"/><path d="M22.5 3.5C20 6 17.5 8.5 16 12"/></svg></div>
          <div>
            <h4>Economía circular</h4>
            <p>Los equipos provienen de renovaciones de flotas corporativas y organismos públicos. Revisados, actualizados y listos.</p>
          </div>
        </div>
        <div class="sostenibilidad-item">
          <div class="sostenibilidad-item-icon"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M12 22V12"/><path d="M12 12C12 7 17 3 21 3c0 5-3 9-9 9z"/><path d="M12 12C12 7 7 3 3 3c0 5 3 9 9 9z"/></svg></div>
          <div>
            <h4>Cero residuos innecesarios</h4>
            <p>Un portátil reacondicionado requiere un 80% menos de energía de fabricación que uno nuevo equivalente.</p>
          </div>
        </div>
        <div class="sostenibilidad-item">
          <div class="sostenibilidad-item-icon"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#22C55E" stroke-width="2.5" style="vertical-align:middle;margin-right:4px"><polyline points="20 6 9 17 4 12"/></svg></div>
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
