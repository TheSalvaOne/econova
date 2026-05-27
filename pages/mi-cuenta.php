<?php
$page_title = 'Mi cuenta';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion();
require_login();

$uid  = (int)$_SESSION['usuario_id'];
$user = db()->prepare('SELECT * FROM usuarios WHERE id=?');
$user->execute([$uid]);
$u = $user->fetch();

$msg = '';
$error = '';

// Cambiar contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $actual  = $_POST['password_actual']  ?? '';
    $nueva   = $_POST['password_nueva']   ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if (!password_verify($actual, $u['password'])) {
        $error = 'La contraseña actual no es correcta.';
    } elseif (strlen($nueva) < 8) {
        $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
    } elseif (!preg_match('/[A-Z]/', $nueva) || !preg_match('/[0-9]/', $nueva)) {
        $error = 'La nueva contraseña debe incluir una mayúscula y un número.';
    } elseif ($nueva !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($nueva, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        db()->prepare('UPDATE usuarios SET password=? WHERE id=?')->execute([$hash, $uid]);
        audit('cambio_password', 'usuarios', $uid);
        $msg = 'Contraseña actualizada correctamente.';
        $u['password'] = $hash;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrf_token() ?>';</script>

<div class="container" style="padding:3rem 1.5rem 5rem; max-width:600px; margin:0 auto">
  <h1 style="margin-bottom:2rem">Mi cuenta</h1>

  <div class="admin-card" style="margin-bottom:1.5rem">
    <h3 style="margin-bottom:1rem">Información de la cuenta</h3>
    <div class="spec-row"><span class="spec-key">Nombre</span><span class="spec-val"><?= e($u['nombre']) ?></span></div>
    <div class="spec-row"><span class="spec-key">Email</span><span class="spec-val"><?= e($u['email']) ?></span></div>
    <div class="spec-row"><span class="spec-key">Rol</span><span class="spec-val"><?= e(ucfirst($u['rol'])) ?></span></div>
    <div class="spec-row"><span class="spec-key">Miembro desde</span><span class="spec-val"><?= date('d/m/Y', strtotime($u['created_at'])) ?></span></div>
  </div>

  <div class="admin-card">
    <h3 style="margin-bottom:1rem">Cambiar contraseña</h3>
    <?php if ($msg): ?><div class="form-success"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Contraseña actual</label>
        <input type="password" name="password_actual" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Nueva contraseña</label>
        <input type="password" name="password_nueva" class="form-control" required minlength="8">
        <small style="color:var(--gris-medio); font-size:.75rem">Mínimo 8 caracteres, una mayúscula y un número.</small>
      </div>
      <div class="form-group">
        <label>Confirmar nueva contraseña</label>
        <input type="password" name="password_confirm" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary">Actualizar contraseña</button>
    </form>
  </div>

  <div style="margin-top:1.5rem; display:flex; gap:1rem; flex-wrap:wrap">
    <a href="<?= BASE_URL ?>/pages/mis-presupuestos.php" class="btn btn-outline">Mis presupuestos</a>
    <a href="<?= BASE_URL ?>/pages/favoritos.php" class="btn btn-outline">Mis favoritos</a>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
