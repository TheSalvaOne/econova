<?php
$page_title = 'Contacto';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion();

$enviado = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $nombre  = trim(strip_tags($_POST['nombre']  ?? ''));
    $email   = sanitize_email($_POST['email']   ?? '');
    $asunto  = trim(strip_tags($_POST['asunto']  ?? ''));
    $mensaje = trim(strip_tags($_POST['mensaje'] ?? ''));

    if (!$nombre || !$email || !$asunto || !$mensaje) {
        $error = 'Rellena todos los campos.';
    } elseif (strlen($mensaje) > 2000) {
        $error = 'El mensaje es demasiado largo (máx. 2000 caracteres).';
    } else {
        // En producción: enviar email con mail() o PHPMailer
        // Aquí lo guardamos en audit_log como demo
        audit('contacto_form');
        $enviado = true;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrf_token() ?>';</script>

<div class="container" style="padding:3rem 1.5rem 5rem">

  <div style="display:grid; grid-template-columns:1fr 1fr; gap:5rem; align-items:start; max-width:900px; margin:0 auto">

    <div>
      <span class="section-number">Contacto</span>
      <h1 style="margin:.5rem 0 1.5rem">Hablemos</h1>
      <p style="color:var(--gris-medio); line-height:1.8; font-weight:300">
        ¿Tienes alguna pregunta sobre un equipo? ¿Necesitas un presupuesto para empresa?
        Escríbenos y te respondemos en menos de 24h.
      </p>
      <div style="margin-top:2rem; display:flex; flex-direction:column; gap:1rem">
        <div style="display:flex; gap:.75rem; align-items:center">
          <span style="font-size:1.5rem">📧</span>
          <div>
            <div style="font-weight:700; font-size:.9rem">Email</div>
            <div style="color:var(--gris-medio); font-size:.875rem">hola@econova.local</div>
          </div>
        </div>
        <div style="display:flex; gap:.75rem; align-items:center">
          <span style="font-size:1.5rem">📍</span>
          <div>
            <div style="font-weight:700; font-size:.9rem">Dirección</div>
            <div style="color:var(--gris-medio); font-size:.875rem">Calle Ejemplo 1, Madrid</div>
          </div>
        </div>
        <div style="display:flex; gap:.75rem; align-items:center">
          <span style="font-size:1.5rem">⏰</span>
          <div>
            <div style="font-weight:700; font-size:.9rem">Horario</div>
            <div style="color:var(--gris-medio); font-size:.875rem">Lun–Vie, 9:00–18:00</div>
          </div>
        </div>
      </div>
    </div>

    <div>
      <?php if ($enviado): ?>
        <div class="form-success" style="padding:2rem; text-align:center">
          <div style="font-size:2rem; margin-bottom:.75rem">✅</div>
          <h3>¡Mensaje enviado!</h3>
          <p style="margin-top:.5rem; color:var(--gris-medio)">Te respondemos en menos de 24h.</p>
        </div>
      <?php else: ?>
        <?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>
        <form method="POST">
          <?= csrf_field() ?>
          <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" required value="<?= e($_POST['nombre'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Asunto</label>
            <select name="asunto">
              <option value="presupuesto">Solicitar presupuesto</option>
              <option value="consulta">Consulta sobre producto</option>
              <option value="garantia">Garantía o soporte</option>
              <option value="empresa">Compra para empresa</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          <div class="form-group">
            <label>Mensaje</label>
            <textarea name="mensaje" rows="5" required maxlength="2000"><?= e($_POST['mensaje'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%">Enviar mensaje</button>
        </form>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
