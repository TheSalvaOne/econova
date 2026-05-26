<?php
// pages/registro.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';

iniciar_sesion();
if (usuario_logueado()) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $nombre   = trim(strip_tags($_POST['nombre'] ?? ''));
    $email    = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (!$nombre || !$email || !$password) {
        $error = 'Rellena todos los campos.';
    } elseif (strlen($nombre) > 100) {
        $error = 'El nombre es demasiado largo.';
    } elseif (!$email) {
        $error = 'El email no es válido.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = 'La contraseña debe incluir al menos una mayúscula y un número.';
    } elseif ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // Verificar email único
        $check = db()->prepare('SELECT id FROM usuarios WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Ese email ya está registrado.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
            $ins  = db()->prepare('INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)');
            $ins->execute([$nombre, $email, $hash]);
            audit('registro', 'usuarios', (int)db()->lastInsertId());
            header('Location: ' . BASE_URL . '/pages/login.php?registered=1'); exit;
        }
    }
}

$page_title = 'Crear cuenta';
require_once __DIR__ . '/../includes/header.php';
?>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrf_token() ?>';</script>

<div class="container" style="padding:4rem 1.5rem">
  <div class="form-card">
    <h2>Crear cuenta</h2>
    <p class="form-subtitle">Accede a favoritos, historial de presupuestos y más.</p>

    <?php if ($error): ?>
      <div class="form-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Nombre completo</label>
        <input type="text" name="nombre" required maxlength="100"
               value="<?= e($_POST['nombre'] ?? '') ?>" autocomplete="name">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required
               value="<?= e($_POST['email'] ?? '') ?>" autocomplete="email">
      </div>
      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="password" required minlength="8" autocomplete="new-password">
        <small style="color:var(--gris-medio); font-size:.75rem; margin-top:.25rem; display:block">
          Mínimo 8 caracteres, una mayúscula y un número.
        </small>
      </div>
      <div class="form-group">
        <label>Confirmar contraseña</label>
        <input type="password" name="confirm" required autocomplete="new-password">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%">Crear cuenta</button>
    </form>

    <p class="form-link">
      ¿Ya tienes cuenta? <a href="<?= BASE_URL ?>/pages/login.php">Inicia sesión</a>
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
