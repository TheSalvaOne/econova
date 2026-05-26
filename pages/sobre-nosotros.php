<?php
$page_title = 'Sobre nosotros';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/header.php';
?>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrf_token() ?>';</script>

<!-- HERO sobre nosotros -->
<section style="background:var(--negro); color:var(--blanco); padding:6rem 0">
  <div class="container">
    <span class="section-number">Nuestra historia</span>
    <h1 style="color:var(--blanco); max-width:700px; margin-top:.5rem">
      La tecnología que <em style="color:var(--naranja)">ya existe</em><br>
      es suficiente.
    </h1>
    <p style="color:#AAAAAA; max-width:560px; font-size:1.1rem; font-weight:300; margin-top:1.5rem; line-height:1.8">
      EcoNova nace con una convicción sencilla: millones de equipos corporativos
      se jubilan antes de tiempo por ciclos de renovación forzada. Nosotros los
      recuperamos, los ponemos a punto y les damos una segunda vida.
    </p>
  </div>
</section>

<!-- Qué somos -->
<section>
  <div class="container">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:5rem; align-items:center">
      <div>
        <span class="section-number">01 — Misión</span>
        <h2 style="margin:.5rem 0 1.5rem">Economía circular aplicada a la tecnología</h2>
        <p style="color:var(--gris-medio); line-height:1.9; font-weight:300">
          Trabajamos con grandes empresas, entidades bancarias y organismos públicos que renuevan
          sus flotas de equipos cada 3-5 años. Estos equipos, lejos de ser obsoletos, son máquinas
          de alto rendimiento en perfecto estado funcional.
        </p>
        <p style="color:var(--gris-medio); line-height:1.9; font-weight:300; margin-top:1rem">
          Cada equipo pasa por nuestro proceso de diagnóstico: limpieza profunda, sustitución
          de componentes con desgaste (batería, disco), test de rendimiento y clasificación
          por grado de reacondicionamiento (A, B o C).
        </p>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem">
        <?php
        $datos = [
          ['500+', 'Equipos reacondicionados'],
          ['70%',  'Ahorro medio vs. nuevo'],
          ['2 años','Garantía en todo'],
          ['0',    'Residuos sin reutilizar'],
        ];
        foreach ($datos as $d):
        ?>
          <div style="background:var(--gris-lite); border-radius:8px; padding:1.5rem; border:1px solid var(--borde)">
            <div style="font-family:var(--font-display); font-size:2.2rem; font-weight:800; color:var(--naranja)"><?= $d[0] ?></div>
            <div style="font-size:.85rem; color:var(--gris-medio); margin-top:.25rem"><?= $d[1] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Proceso -->
<section style="background:var(--blanco)">
  <div class="container">
    <span class="section-number">02 — Proceso</span>
    <h2 style="margin:.5rem 0 3rem">Cómo funciona</h2>
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:2rem">
      <?php
      $pasos = [
        ['01','Recepción','Recibimos lotes de equipos de empresas y organismos públicos.'],
        ['02','Diagnóstico','Test completo de hardware: CPU, RAM, disco, pantalla, batería.'],
        ['03','Reacondicionamiento','Limpieza, sustitución de piezas y actualización de software.'],
        ['04','Clasificación','Asignamos grado A, B o C según el estado final del equipo.'],
      ];
      foreach ($pasos as $p):
      ?>
        <div>
          <div style="font-family:var(--font-display); font-size:3rem; font-weight:800; color:var(--naranja); line-height:1"><?= $p[0] ?></div>
          <h4 style="margin:.75rem 0 .5rem"><?= $p[1] ?></h4>
          <p style="font-size:.875rem; color:var(--gris-medio); line-height:1.7"><?= $p[2] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Impacto ambiental -->
<section class="sostenibilidad-section">
  <div class="container">
    <span class="section-number">03 — Impacto</span>
    <h2 style="color:var(--blanco); margin:.5rem 0 1.5rem">El coste real de fabricar un portátil nuevo</h2>
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:2rem">
      <?php
      $impacto = [
        ['🌍','300 kg de CO₂','emitidos en la fabricación de un portátil medio.'],
        ['💧','190.000 litros','de agua necesarios para fabricar un ordenador de sobremesa.'],
        ['♻ -80%','Menos energía','requiere reacondicionar frente a fabricar de cero.'],
      ];
      foreach ($impacto as $i):
      ?>
        <div style="border:1px solid rgba(255,255,255,.12); border-radius:8px; padding:2rem">
          <div style="font-size:2rem; margin-bottom:.75rem"><?= $i[0] ?></div>
          <div style="font-family:var(--font-display); font-size:1.5rem; font-weight:800; color:var(--naranja); margin-bottom:.5rem"><?= $i[1] ?></div>
          <p style="color:#AAAAAA; font-size:.875rem; font-weight:300"><?= $i[2] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section style="text-align:center">
  <div class="container">
    <h2 style="margin-bottom:1rem">¿Listo para hacer el cambio?</h2>
    <p style="color:var(--gris-medio); margin-bottom:2rem; font-weight:300">Explora nuestro catálogo y encuentra el equipo perfecto con garantía de 2 años.</p>
    <a href="<?= BASE_URL ?>/pages/catalogo.php" class="btn btn-primary">Ver catálogo</a>
    <a href="<?= BASE_URL ?>/pages/contacto.php" class="btn btn-outline" style="margin-left:1rem">Contactar</a>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
