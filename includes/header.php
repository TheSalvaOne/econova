<?php
// ============================================================
// includes/header.php — Cabecera HTML común
// ============================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';
iniciar_sesion();
security_headers();

// Título de página: cada fichero define $page_title antes de incluir
$title = isset($page_title) ? e($page_title) . ' — ' . SITE_NAME : SITE_NAME;

// Carrito: número de items
$carrito_count = 0;
if (usuario_logueado()) {
    $stmt = db()->prepare('SELECT COALESCE(SUM(cantidad),0) FROM carrito WHERE usuario_id=?');
    $stmt->execute([$_SESSION['usuario_id']]);
    $carrito_count = (int)$stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="EcoNova — Equipos tecnológicos reacondicionados con segunda vida">
  <title><?= $title ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<header class="site-header">
  <div class="header-inner">

    <a href="<?= BASE_URL ?>/index.php" class="logo">
      <span class="logo-eco">Eco</span><span class="logo-nova">Nova</span>
    </a>

    <nav class="main-nav">
      <a href="<?= BASE_URL ?>/index.php">Inicio</a>
      <a href="<?= BASE_URL ?>/pages/catalogo.php">Catálogo</a>
      <a href="<?= BASE_URL ?>/pages/sobre-nosotros.php">Nosotros</a>
      <a href="<?= BASE_URL ?>/pages/contacto.php">Contacto</a>
    </nav>

    <div class="header-actions">
      <?php if (usuario_logueado()): ?>
        <a href="<?= BASE_URL ?>/pages/favoritos.php" class="btn-icon" title="Favoritos">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </a>
        <a href="<?= BASE_URL ?>/pages/carrito.php" class="btn-icon btn-carrito" title="Carrito">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
          <?php if ($carrito_count > 0): ?>
            <span class="carrito-badge"><?= $carrito_count ?></span>
          <?php endif; ?>
        </a>
        <div class="user-menu">
          <span class="user-name"><?= e($_SESSION['usuario_nombre']) ?></span>
          <div class="user-dropdown">
            <a href="<?= BASE_URL ?>/pages/mi-cuenta.php">Mi cuenta</a>
            <a href="<?= BASE_URL ?>/pages/mis-presupuestos.php">Mis presupuestos</a>
            <?php if (usuario_admin()): ?>
              <a href="<?= BASE_URL ?>/admin/index.php">Panel admin</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/pages/logout.php">Cerrar sesión</a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/pages/login.php" class="btn btn-outline">Entrar</a>
        <a href="<?= BASE_URL ?>/pages/registro.php" class="btn btn-primary">Registrarse</a>
      <?php endif; ?>
    </div>

    <button class="hamburger" aria-label="Menú" onclick="toggleMenu()">
      <span></span><span></span><span></span>
    </button>

  </div>
</header>

<main>
<?php
// ============================================================
// includes/footer.php — Pie de página común
// ============================================================
?>
</main><!-- cierre de <main> viene desde footer.php -->
<?php /* footer se incluye aparte */ ?>
