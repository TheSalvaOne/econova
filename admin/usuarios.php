<?php
// admin/usuarios.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion(); require_admin();

// Activar/desactivar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_activo'])) {
    csrf_verify();
    $uid = sanitize_int($_POST['usuario_id']);
    // No permitir desactivar al propio admin logueado
    if ($uid !== (int)$_SESSION['usuario_id']) {
        db()->prepare('UPDATE usuarios SET activo = NOT activo WHERE id=?')->execute([$uid]);
        audit('usuario_toggle', 'usuarios', $uid);
    }
    header('Location: ' . BASE_URL . '/admin/usuarios.php'); exit;
}

$busq  = trim(strip_tags($_GET['q'] ?? ''));
$where = $busq ? 'WHERE (nombre LIKE ? OR email LIKE ?)' : '';
$pars  = $busq ? ['%'.$busq.'%', '%'.$busq.'%'] : [];

$stmt = db()->prepare(
    "SELECT u.*,
            (SELECT COUNT(*) FROM presupuestos p WHERE p.usuario_id=u.id) AS num_presupuestos,
            (SELECT COUNT(*) FROM favoritos f WHERE f.usuario_id=u.id) AS num_favoritos
     FROM usuarios u
     $where
     ORDER BY u.created_at DESC"
);
$stmt->execute($pars);
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios — Admin EcoNova</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <a href="<?= BASE_URL ?>/index.php" class="logo">
      <span class="logo-eco">Eco</span><span class="logo-nova" style="color:#fff">Nova</span>
    </a>
    <nav class="admin-nav">
      <a href="<?= BASE_URL ?>/admin/index.php">📊 Dashboard</a>
      <a href="<?= BASE_URL ?>/admin/productos.php">📦 Productos</a>
      <a href="<?= BASE_URL ?>/admin/presupuestos.php">📋 Presupuestos</a>
      <a href="<?= BASE_URL ?>/admin/usuarios.php" class="active">👥 Usuarios</a>
      <a href="<?= BASE_URL ?>/admin/audit.php">🔍 Audit log</a>
      <hr style="border-color:rgba(255,255,255,.1); margin:1rem 0">
      <a href="<?= BASE_URL ?>/index.php">🌐 Ver web</a>
      <a href="<?= BASE_URL ?>/pages/logout.php">🚪 Cerrar sesión</a>
    </nav>
  </aside>

  <div class="admin-content">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem">
      <div>
        <h1>Usuarios</h1>
        <p style="color:var(--gris-medio); font-size:.875rem"><?= count($usuarios) ?> usuarios registrados</p>
      </div>
      <form method="GET" style="display:flex; gap:.5rem">
        <input type="search" name="q" value="<?= e($busq) ?>"
               placeholder="Buscar por nombre o email..."
               style="padding:.5rem .875rem; border:1px solid var(--borde); border-radius:4px; font-family:var(--font-body); min-width:220px">
        <button type="submit" class="btn btn-outline btn-sm">Buscar</button>
        <?php if ($busq): ?>
          <a href="<?= BASE_URL ?>/admin/usuarios.php" class="btn btn-ghost btn-sm">✕ Limpiar</a>
        <?php endif; ?>
      </form>
    </div>

    <div class="admin-card">
      <table class="tabla-admin">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Presupuestos</th>
            <th>Favoritos</th>
            <th>Registro</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($usuarios)): ?>
            <tr>
              <td colspan="9" style="text-align:center; color:var(--gris-medio); padding:2rem">
                Sin resultados para "<?= e($busq) ?>".
              </td>
            </tr>
          <?php else: foreach ($usuarios as $u): ?>
            <tr <?= !$u['activo'] ? 'style="opacity:.45"' : '' ?>>
              <td style="font-size:.78rem; color:var(--gris-medio)">#<?= $u['id'] ?></td>
              <td style="font-weight:600"><?= e($u['nombre']) ?></td>
              <td style="font-size:.85rem"><?= e($u['email']) ?></td>
              <td>
                <?php if ($u['rol'] === 'admin'): ?>
                  <span class="badge" style="background:var(--naranja-lite); color:var(--naranja-dark)">Admin</span>
                <?php else: ?>
                  <span class="badge" style="background:var(--gris-lite); color:var(--gris-medio)">Cliente</span>
                <?php endif; ?>
              </td>
              <td style="text-align:center">
                <?php if ($u['num_presupuestos'] > 0): ?>
                  <a href="<?= BASE_URL ?>/admin/presupuestos.php"
                     style="color:var(--naranja); font-weight:700"><?= $u['num_presupuestos'] ?></a>
                <?php else: ?>
                  <span style="color:var(--gris-medio)">0</span>
                <?php endif; ?>
              </td>
              <td style="text-align:center; color:var(--gris-medio)"><?= $u['num_favoritos'] ?></td>
              <td style="font-size:.78rem; color:var(--gris-medio)">
                <?= date('d/m/Y', strtotime($u['created_at'])) ?>
              </td>
              <td>
                <?php if ($u['activo']): ?>
                  <span class="badge" style="background:#D4F0D4; color:#1A6B1A">Activo</span>
                <?php else: ?>
                  <span class="badge" style="background:#FFE5E5; color:#8B1A1A">Inactivo</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($u['id'] !== (int)$_SESSION['usuario_id'] && $u['rol'] !== 'admin'): ?>
                  <form method="POST" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
                    <button name="toggle_activo" value="1"
                            class="btn btn-outline btn-sm"
                            onclick="return confirm('¿Cambiar estado del usuario?')">
                      <?= $u['activo'] ? 'Desactivar' : 'Activar' ?>
                    </button>
                  </form>
                <?php else: ?>
                  <span style="font-size:.78rem; color:var(--gris-medio)">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
