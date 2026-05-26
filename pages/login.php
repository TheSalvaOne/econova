<?php
// ============================================================
// pages/login.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';

iniciar_sesion();
if (usuario_logueado()) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$error = '';
$next  = $_GET['next'] ?? (BASE_URL . '/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email    = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip_key   = 'login_' . md5($_SERVER['REMOTE_ADDR'] ?? '');

    if (!$email || !$password) {
        $error = 'Rellena todos los campos.';
    } elseif (!check_rate_limit($ip_key)) {
        $error = 'Demasiados intentos. Espera 15 minutos.';
    } else {
        $stmt = db()->prepare('SELECT * FROM usuarios WHERE email = ? AND activo = 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            reset_rate_limit($ip_key);
            login_usuario($user);
            audit('login', 'usuarios', $user['id']);
            $dest = filter_var($next, FILTER_VALIDATE_URL) ? $next : BASE_URL . '/index.php';
            header('Location: ' . $dest); exit;
        } else {
            increment_rate_limit($ip_key);
            audit('login_fail', 'usuarios');
            $error = 'Email o contraseña incorrectos.';
            usleep(500000); // delay anti-brute-force
        }
    }
}

$page_title = 'Iniciar sesión';
require_once __DIR__ . '/../includes/header.php';
?>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrf_token() ?>';</script>

<div class="container" style="padding:4rem 1.5rem">
  <div class="form-card">
    <h2>Bienvenido de nuevo</h2>
    <p class="form-subtitle">Accede a tu cuenta para ver favoritos, presupuestos y más.</p>

    <?php if ($error): ?>
      <div class="form-error"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['registered'])): ?>
      <div class="form-success">✅ Cuenta creada. Ya puedes entrar.</div>
    <?php endif; ?>

    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required autocomplete="email"
               value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%">Entrar</button>
    </form>

    <p class="form-link">
      ¿No tienes cuenta? <a href="<?= BASE_URL ?>/pages/registro.php">Regístrate gratis</a>
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
