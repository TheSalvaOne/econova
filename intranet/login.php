<?php
// login.php — Acceso a la intranet
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

intranet_session();
if (logueado()) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['usuario'] ?? '');
    $pass = $_POST['password'] ?? '';

    // Rate limiting simple por sesión
    $_SESSION['login_intentos'] = ($_SESSION['login_intentos'] ?? 0);
    if ($_SESSION['login_intentos'] >= 5) {
        $error = 'Demasiados intentos. Reinicia el navegador o espera.';
    } elseif ($user === INTRANET_USER && password_verify($pass, INTRANET_PASS)) {
        session_regenerate_id(true);
        $_SESSION['intranet_auth'] = true;
        $_SESSION['login_intentos'] = 0;
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    } else {
        $_SESSION['login_intentos']++;
        usleep(400000); // delay anti-brute-force
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="robots" content="noindex, nofollow">
  <title>Acceso — <?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/intranet.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <div class="login-logo">
      <div class="logo-mark">EN</div>
      <div>
        <div class="logo-name">EcoNova</div>
        <div class="logo-sub">Intranet corporativa</div>
      </div>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Usuario</label>
        <input type="text" name="usuario" class="form-control"
               autocomplete="username" required
               value="<?= e($_POST['usuario'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="password" class="form-control"
               autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; margin-top:.5rem">
        Acceder →
      </button>
    </form>

    <p style="margin-top:1.5rem; font-size:.72rem; color:var(--txt-3); font-family:var(--font-mono); text-align:center;">
      Acceso restringido a red interna y Tailscale.<br>
      Solo personal autorizado de EcoNova.
    </p>
  </div>
</div>
</body>
</html>
