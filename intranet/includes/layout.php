<?php
// includes/layout.php — Cabecera y pie compartidos de la intranet
// Uso: require_once layout_head('Título de página');  ... require_once layout_foot();
// O directamente con las funciones layout_open() / layout_close()

function layout_open(string $page_title = ''): void {
    $title = $page_title ? e($page_title) . ' — ' . SITE_NAME : SITE_NAME;
    $nav_items = [
        'index.php'         => ['icon' => '⬡', 'label' => 'Dashboard'],
        'pages/servicios.php' => ['icon' => '◈', 'label' => 'Servicios'],
        'pages/ftp.php'     => ['icon' => '◫', 'label' => 'Archivos FTP'],
        'pages/tareas.php'  => ['icon' => '◻', 'label' => 'Tareas'],
        'pages/documentos.php' => ['icon' => '◪', 'label' => 'Documentos'],
    ];
    $current = basename($_SERVER['PHP_SELF']);
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= $title ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/intranet.css">
</head>
<body>

<div class="shell">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <span class="logo-mark">EN</span>
      <div>
        <div class="logo-name">EcoNova</div>
        <div class="logo-sub">Intranet</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <?php foreach ($nav_items as $href => $item): ?>
        <?php $active = (basename($href) === $current || $href === $_SERVER['PHP_SELF']) ? 'active' : ''; ?>
        <a href="<?= BASE_URL ?>/<?= $href ?>" class="nav-item <?= $active ?>">
          <span class="nav-icon"><?= $item['icon'] ?></span>
          <span><?= $item['label'] ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="user-avatar">A</div>
        <div>
          <div class="user-name">Admin</div>
          <div class="user-role">Administrador</div>
        </div>
      </div>
      <a href="<?= BASE_URL ?>/logout.php" class="btn-logout" title="Cerrar sesión">⏻</a>
    </div>
  </aside>

  <!-- Contenido principal -->
  <div class="main">
    <header class="topbar">
      <div class="topbar-left">
        <h1 class="page-title"><?= e($page_title ?: 'Dashboard') ?></h1>
        <span class="topbar-date"><?= date('l, j \d\e F \d\e Y') ?></span>
      </div>
      <div class="topbar-right">
        <div class="status-dot <?= host_ping(VM_WEB_IP) ? 'online' : 'offline' ?>"></div>
        <span class="status-label">VM Web <?= host_ping(VM_WEB_IP) ? 'online' : 'offline' ?></span>
        <a href="<?= BASE_URL ?>/index.php" class="topbar-link">↺ Refresh</a>
      </div>
    </header>
    <div class="content">
    <?php
}

function layout_close(): void {
    ?>
    </div><!-- .content -->
  </div><!-- .main -->
</div><!-- .shell -->

<script src="<?= BASE_URL ?>/assets/js/intranet.js"></script>
</body>
</html>
    <?php
}
