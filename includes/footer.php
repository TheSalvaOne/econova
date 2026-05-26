<?php
// includes/footer.php
?>
</main>

<footer class="site-footer">
  <div class="footer-inner">

    <div class="footer-brand">
      <a href="<?= BASE_URL ?>/index.php" class="logo">
        <span class="logo-eco">Eco</span><span class="logo-nova">Nova</span>
      </a>
      <p>Tecnología con segunda vida.<br>Sostenibilidad sin renunciar al rendimiento.</p>
      <div class="footer-sostenibilidad">
        <span>♻ CO₂ ahorrado por reutilización</span>
        <span>🌱 0 residuos electrónicos innecesarios</span>
      </div>
    </div>

    <div class="footer-links">
      <div>
        <h4>Catálogo</h4>
        <a href="<?= BASE_URL ?>/pages/catalogo.php?cat=ordenadores">Ordenadores</a>
        <a href="<?= BASE_URL ?>/pages/catalogo.php?cat=portatiles">Portátiles</a>
        <a href="<?= BASE_URL ?>/pages/catalogo.php?cat=monitores">Monitores</a>
        <a href="<?= BASE_URL ?>/pages/catalogo.php?cat=servidores">Servidores</a>
        <a href="<?= BASE_URL ?>/pages/catalogo.php?cat=accesorios">Accesorios</a>
      </div>
      <div>
        <h4>EcoNova</h4>
        <a href="<?= BASE_URL ?>/pages/sobre-nosotros.php">Sobre nosotros</a>
        <a href="<?= BASE_URL ?>/pages/contacto.php">Contacto</a>
        <a href="<?= BASE_URL ?>/pages/como-funciona.php">Cómo funciona</a>
      </div>
      <div>
        <h4>Mi cuenta</h4>
        <a href="<?= BASE_URL ?>/pages/login.php">Entrar</a>
        <a href="<?= BASE_URL ?>/pages/registro.php">Registrarse</a>
        <a href="<?= BASE_URL ?>/pages/mis-presupuestos.php">Mis presupuestos</a>
        <a href="<?= BASE_URL ?>/pages/favoritos.php">Favoritos</a>
      </div>
    </div>

  </div>
  <div class="footer-bottom">
    <p>&copy; <?= date('Y') ?> EcoNova — Proyecto Intermodular 2º SMR · CDM FP</p>
  </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
