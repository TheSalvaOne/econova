// ============================================================
// EcoNova — main.js
// ============================================================

'use strict';

// ── Menú mobile ──────────────────────────────────────────────
function toggleMenu() {
  const nav = document.getElementById('mobile-nav');
  if (nav) nav.classList.toggle('open');
}

// ── Favoritos (AJAX) ─────────────────────────────────────────
function toggleFavorito(productoId, btn) {
  fetch(BASE_URL + '/pages/favorito-toggle.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'producto_id=' + productoId + '&csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.ok) {
      btn.classList.toggle('activo', data.favorito);
      // pequeño feedback visual
      btn.style.transform = 'scale(1.3)';
      setTimeout(() => btn.style.transform = '', 200);
    } else if (data.redirect) {
      window.location.href = data.redirect;
    }
  })
  .catch(() => console.error('Error al actualizar favorito'));
}

// ── Carrito: actualizar cantidad ─────────────────────────────
function actualizarCantidad(productoId, cantidad) {
  fetch(BASE_URL + '/pages/carrito-update.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'producto_id=' + productoId + '&cantidad=' + cantidad + '&csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.ok) {
      // Actualizar badge del header
      const badge = document.querySelector('.carrito-badge');
      if (badge) badge.textContent = data.total_items;
      // Recargar resumen si existe
      if (document.getElementById('carrito-resumen')) {
        location.reload();
      }
    }
  });
}

// ── Filtros catálogo: submit automático ──────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const filtrosForm = document.getElementById('filtros-form');
  if (filtrosForm) {
    filtrosForm.querySelectorAll('input[type="checkbox"], select').forEach(el => {
      el.addEventListener('change', () => filtrosForm.submit());
    });
  }

  // Animaciones fade-in al hacer scroll
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.opacity = '1';
        e.target.style.transform = 'translateY(0)';
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.producto-card, .categoria-card, .sostenibilidad-item').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(16px)';
    el.style.transition = 'opacity .4s ease, transform .4s ease';
    observer.observe(el);
  });
});

// ── Confirmar eliminación ────────────────────────────────────
function confirmarEliminar(msg) {
  return confirm(msg || '¿Seguro que quieres eliminar este elemento?');
}
