'use strict';

// ── Auto-refresh del dashboard cada 30s ─────────────────────
let refreshInterval = null;

function startAutoRefresh(secs = 30) {
  clearInterval(refreshInterval);
  let countdown = secs;
  const el = document.getElementById('refresh-countdown');

  refreshInterval = setInterval(() => {
    countdown--;
    if (el) el.textContent = countdown + 's';
    if (countdown <= 0) {
      window.location.reload();
    }
  }, 1000);
}

// ── Animar barras de progreso al cargar ─────────────────────
document.addEventListener('DOMContentLoaded', () => {
  // Barras de progreso: animar desde 0
  document.querySelectorAll('.progress-fill').forEach(bar => {
    const target = bar.style.width;
    bar.style.width = '0';
    setTimeout(() => { bar.style.width = target; }, 100);
  });

  // Toggle tarea completada (AJAX)
  document.querySelectorAll('.tarea-check').forEach(btn => {
    btn.addEventListener('click', () => {
      const id    = btn.dataset.id;
      const done  = btn.classList.contains('done');
      const token = document.querySelector('meta[name="csrf"]')?.content;

      fetch('/pages/tarea-toggle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&done=${done ? 0 : 1}&csrf=${token}`,
      })
      .then(r => r.json())
      .then(d => {
        if (d.ok) {
          btn.classList.toggle('done');
          const titulo = btn.closest('.tarea-row')?.querySelector('.tarea-titulo');
          if (titulo) titulo.classList.toggle('done');
        }
      });
    });
  });

  // Activar auto-refresh si existe el elemento countdown
  if (document.getElementById('refresh-countdown')) {
    startAutoRefresh(30);
  }
});

// ── Confirmar acciones destructivas ─────────────────────────
function confirmar(msg) {
  return confirm(msg || '¿Seguro que quieres continuar?');
}
