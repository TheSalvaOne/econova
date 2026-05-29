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
        <span><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><polyline points="1.5 8.5 1.5 3.5 6.5 3.5"/><path d="M1.5 3.5C3.5 6 6 8 9 9"/><polyline points="22.5 15.5 22.5 20.5 17.5 20.5"/><path d="M22.5 20.5C20.5 18 18 16 15 15"/><polyline points="6.5 20.5 1.5 20.5 1.5 15.5"/><path d="M1.5 20.5C4 18 6.5 15.5 8 12"/><polyline points="17.5 3.5 22.5 3.5 22.5 8.5"/><path d="M22.5 3.5C20 6 17.5 8.5 16 12"/></svg> CO₂ ahorrado por reutilización</span>
        <span><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><path d="M12 22V12"/><path d="M12 12C12 7 17 3 21 3c0 5-3 9-9 9z"/><path d="M12 12C12 7 7 3 3 3c0 5 3 9 9 9z"/></svg> 0 residuos electrónicos innecesarios</span>
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
