<?php
$page_title = 'Cómo funciona';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/header.php';
?>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrf_token() ?>';</script>

<section style="padding:4rem 0">
  <div class="container" style="max-width:800px">
    <span class="section-number">El proceso</span>
    <h1 style="margin:.5rem 0 2rem">Cómo funciona EcoNova</h1>

    <?php
    $pasos = [
      ['01','Selecciona tu equipo','Navega por nuestro catálogo y filtra por categoría, grado o precio. Cada ficha incluye especificaciones completas y el grado de reacondicionamiento (A, B o C).'],
      ['02','Añade al carrito','Guarda los equipos que te interesan en el carrito. Puedes modificar cantidades o eliminar productos antes de solicitar el presupuesto.'],
      ['03','Solicita presupuesto','EcoNova no procesa pagos online. El carrito se convierte en una solicitud de presupuesto que nuestro equipo revisa manualmente.'],
      ['04','Revisamos y contactamos','En menos de 24-48h un técnico de EcoNova revisa tu solicitud y se pone en contacto contigo para confirmar disponibilidad, precio final y forma de pago.'],
      ['05','Recibe tu equipo','El equipo sale revisado, certificado y con 2 años de garantía incluida. Envío en 24-48h a la península.'],
    ];
    foreach ($pasos as $i => $p):
    ?>
    <div style="display:flex; gap:1.5rem; margin-bottom:2.5rem; align-items:flex-start">
      <div style="font-family:var(--font-display); font-size:2.5rem; font-weight:800; color:var(--naranja); line-height:1; flex-shrink:0; width:60px">
        <?= $p[0] ?>
      </div>
      <div style="border-top:2px solid <?= $i===0 ? 'var(--naranja)' : 'var(--borde)' ?>; padding-top:1rem; flex:1">
        <h3 style="margin-bottom:.5rem"><?= $p[1] ?></h3>
        <p style="color:var(--gris-medio); line-height:1.8; font-weight:300"><?= $p[2] ?></p>
      </div>
    </div>
    <?php endforeach; ?>

    <div style="background:var(--naranja-lite); border-radius:8px; padding:2rem; margin-top:2rem; border-left:4px solid var(--naranja)">
      <h3 style="margin-bottom:.75rem">¿Por qué presupuesto y no compra directa?</h3>
      <p style="color:var(--gris-medio); line-height:1.8; font-weight:300">
        Cada equipo reacondicionado es único. El stock puede cambiar, y queremos asegurarnos de que el equipo que recibes cumple exactamente tus expectativas antes de procesar ningún pago. Así podemos ofrecerte la mejor experiencia y garantía.
      </p>
    </div>

    <div style="margin-top:3rem; text-align:center">
      <a href="<?= BASE_URL ?>/pages/catalogo.php" class="btn btn-primary">Explorar el catálogo</a>
      <a href="<?= BASE_URL ?>/pages/contacto.php" class="btn btn-outline" style="margin-left:1rem">Contactar</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
