<?php
// pages/tareas.php — Gestión de tareas internas
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

intranet_session();
require_auth();

// Almacenamos tareas en un JSON en disco (sin BD extra)
$tareas_file = __DIR__ . '/../data/tareas.json';
if (!is_dir(dirname($tareas_file))) mkdir(dirname($tareas_file), 0755, true);

$tareas = file_exists($tareas_file)
    ? json_decode(file_get_contents($tareas_file), true) ?? []
    : [];

$msg = '';

// ── Añadir tarea ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_tarea'])) {
    csrf_verify();
    $titulo   = trim(strip_tags($_POST['titulo'] ?? ''));
    $prioridad = in_array($_POST['prioridad'] ?? '', ['alta','media','baja']) ? $_POST['prioridad'] : 'media';
    if ($titulo) {
        $tareas[] = [
            'id'       => uniqid(),
            'titulo'   => $titulo,
            'prioridad'=> $prioridad,
            'done'     => false,
            'fecha'    => date('Y-m-d H:i'),
        ];
        file_put_contents($tareas_file, json_encode($tareas, JSON_PRETTY_PRINT));
        $msg = 'Tarea añadida.';
    }
}

// ── Eliminar tarea ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    csrf_verify();
    $del_id = $_POST['eliminar_id'];
    $tareas = array_values(array_filter($tareas, fn($t) => $t['id'] !== $del_id));
    file_put_contents($tareas_file, json_encode($tareas, JSON_PRETTY_PRINT));
}

// ── Toggle completada (AJAX) ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    // Solo vía AJAX desde intranet.js
    $tid  = $_POST['toggle_id'];
    $done = (bool)(int)$_POST['done'];
    foreach ($tareas as &$t) {
        if ($t['id'] === $tid) { $t['done'] = $done; break; }
    }
    unset($t);
    file_put_contents($tareas_file, json_encode($tareas, JSON_PRETTY_PRINT));
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]); exit;
}

$pendientes = array_filter($tareas, fn($t) => !$t['done']);
$completadas = array_filter($tareas, fn($t) => $t['done']);

layout_open('Tareas');
?>

<meta name="csrf" content="<?= csrf_token() ?>">

<?php if ($msg): ?><div class="alert alert-ok"><?= e($msg) ?></div><?php endif; ?>

<div class="grid-2">

  <!-- Lista de tareas -->
  <div>
    <div class="card" style="margin-bottom:1rem">
      <div class="section-header">
        <span class="card-title">Pendientes (<?= count($pendientes) ?>)</span>
      </div>
      <?php if (empty($pendientes)): ?>
        <p style="color:var(--txt-3); font-size:.83rem; padding:.5rem 0">Sin tareas pendientes. ✓</p>
      <?php endif; ?>
      <?php foreach ($pendientes as $t): ?>
        <div class="tarea-row">
          <div class="tarea-check" data-id="<?= e($t['id']) ?>"></div>
          <div class="tarea-body">
            <div class="tarea-titulo"><?= e($t['titulo']) ?></div>
            <div class="tarea-meta"><?= e($t['fecha']) ?></div>
          </div>
          <span class="tarea-prioridad <?= e($t['prioridad']) ?>"><?= e($t['prioridad']) ?></span>
          <form method="POST" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="eliminar_id" value="<?= e($t['id']) ?>">
            <button class="btn btn-danger btn-sm" onclick="return confirmar('¿Eliminar esta tarea?')">✕</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($completadas)): ?>
    <div class="card">
      <div class="card-title">Completadas (<?= count($completadas) ?>)</div>
      <?php foreach ($completadas as $t): ?>
        <div class="tarea-row">
          <div class="tarea-check done" data-id="<?= e($t['id']) ?>"></div>
          <div class="tarea-body">
            <div class="tarea-titulo done"><?= e($t['titulo']) ?></div>
            <div class="tarea-meta"><?= e($t['fecha']) ?></div>
          </div>
          <span class="tarea-prioridad <?= e($t['prioridad']) ?>"><?= e($t['prioridad']) ?></span>
          <form method="POST" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="eliminar_id" value="<?= e($t['id']) ?>">
            <button class="btn btn-danger btn-sm" onclick="return confirmar('¿Eliminar?')">✕</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Nueva tarea -->
  <div class="card" style="align-self:start">
    <div class="card-title">Nueva tarea</div>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Descripción</label>
        <input type="text" name="titulo" class="form-control"
               placeholder="Ej: Revisar logs de Apache" required maxlength="200">
      </div>
      <div class="form-group">
        <label>Prioridad</label>
        <select name="prioridad" class="form-control">
          <option value="alta">🔴 Alta</option>
          <option value="media" selected>🟡 Media</option>
          <option value="baja">🟢 Baja</option>
        </select>
      </div>
      <button type="submit" name="nueva_tarea" value="1" class="btn btn-primary" style="width:100%">
        + Añadir tarea
      </button>
    </form>
  </div>

</div>

<script>
// Override toggle para esta página — usa toggle_id en lugar de id
document.querySelectorAll('.tarea-check').forEach(btn => {
  btn.addEventListener('click', () => {
    const id   = btn.dataset.id;
    const done = btn.classList.contains('done') ? 0 : 1;
    fetch('', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `toggle_id=${id}&done=${done}&csrf=<?= csrf_token() ?>`
    })
    .then(r => r.json())
    .then(d => { if (d.ok) location.reload(); });
  });
});
</script>

<?php layout_close(); ?>
