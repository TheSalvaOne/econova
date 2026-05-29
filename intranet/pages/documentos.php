<?php
// pages/documentos.php — Recursos y documentos internos
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

intranet_session();
require_auth();

$docs_file = __DIR__ . '/../data/documentos.json';
if (!is_dir(dirname($docs_file))) mkdir(dirname($docs_file), 0755, true);

$docs = file_exists($docs_file)
    ? json_decode(file_get_contents($docs_file), true) ?? []
    : [];

$msg = '';

// Añadir documento / enlace
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_doc'])) {
    csrf_verify();
    $titulo = trim(strip_tags($_POST['titulo'] ?? ''));
    $url    = filter_var(trim($_POST['url'] ?? ''), FILTER_VALIDATE_URL) ?: '';
    $tipo   = in_array($_POST['tipo'] ?? '', ['enlace','nota','manual','config']) ? $_POST['tipo'] : 'enlace';
    $notas  = trim(strip_tags($_POST['notas'] ?? ''));

    if ($titulo) {
        $docs[] = [
            'id'    => uniqid(),
            'titulo'=> $titulo,
            'url'   => $url,
            'tipo'  => $tipo,
            'notas' => $notas,
            'fecha' => date('Y-m-d H:i'),
        ];
        file_put_contents($docs_file, json_encode($docs, JSON_PRETTY_PRINT));
        $msg = 'Recurso añadido.';
    }
}

// Eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_doc'])) {
    csrf_verify();
    $docs = array_values(array_filter($docs, fn($d) => $d['id'] !== $_POST['eliminar_doc']));
    file_put_contents($docs_file, json_encode($docs, JSON_PRETTY_PRINT));
}

// Recursos por defecto (pre-cargados si no hay nada)
if (empty($docs)) {
    $docs = [
        ['id'=>'default1','titulo'=>'Web EcoNova (pública)',    'url'=>'http://'.VM_WEB_IP.'/econova',        'tipo'=>'enlace','notas'=>'Plataforma pública de venta de equipos reacondicionados.','fecha'=>date('Y-m-d')],
        ['id'=>'default2','titulo'=>'phpMyAdmin',               'url'=>'http://'.VM_WEB_IP.'/phpmyadmin',     'tipo'=>'enlace','notas'=>'Gestión visual de la base de datos MySQL de EcoNova.',   'fecha'=>date('Y-m-d')],
        ['id'=>'default3','titulo'=>'Panel Admin EcoNova',      'url'=>'http://'.VM_WEB_IP.'/econova/admin',  'tipo'=>'enlace','notas'=>'Acceso al panel de administración de la web.',           'fecha'=>date('Y-m-d')],
        ['id'=>'default4','titulo'=>'Documentación BIND9',      'url'=>'https://bind9.readthedocs.io',        'tipo'=>'manual','notas'=>'Manual oficial del servidor DNS BIND9.',                  'fecha'=>date('Y-m-d')],
        ['id'=>'default5','titulo'=>'Guía vsftpd',              'url'=>'https://help.ubuntu.com/community/vsftpd','tipo'=>'manual','notas'=>'Configuración del servidor FTP vsftpd en Ubuntu.',  'fecha'=>date('Y-m-d')],
        ['id'=>'default6','titulo'=>'Tailscale Admin',          'url'=>'https://login.tailscale.com',         'tipo'=>'enlace','notas'=>'Panel de gestión de la red Tailscale (Zero Trust VPN).', 'fecha'=>date('Y-m-d')],
    ];
}

$iconos_tipo = ['enlace'=>'🔗', 'nota'=>'📝', 'manual'=>'📘', 'config'=>'⚙'];

layout_open('Documentos y recursos');
?>

<meta name="csrf" content="<?= csrf_token() ?>">

<?php if ($msg): ?><div class="alert alert-ok"><?= e($msg) ?></div><?php endif; ?>

<div class="grid-2">

  <!-- Listado de recursos -->
  <div>
    <div class="card">
      <div class="card-title">Recursos internos (<?= count($docs) ?>)</div>
      <table class="tabla">
        <thead>
          <tr>
            <th>Tipo</th>
            <th>Recurso</th>
            <th>Notas</th>
            <th>Fecha</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($docs as $d): ?>
            <tr>
              <td style="font-size:1.1rem"><?= $iconos_tipo[$d['tipo']] ?? '📄' ?></td>
              <td>
                <div style="font-weight:600; color:var(--txt-1)">
                  <?php if ($d['url']): ?>
                    <a href="<?= e($d['url']) ?>" target="_blank"
                       style="color:var(--naranja)"><?= e($d['titulo']) ?> ↗</a>
                  <?php else: ?>
                    <?= e($d['titulo']) ?>
                  <?php endif; ?>
                </div>
                <?php if ($d['url']): ?>
                  <div class="mono" style="font-size:.68rem; color:var(--txt-3); margin-top:.2rem">
                    <?= e($d['url']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td style="color:var(--txt-3); font-size:.8rem"><?= e($d['notas']) ?></td>
              <td class="mono" style="font-size:.72rem"><?= e($d['fecha']) ?></td>
              <td>
                <form method="POST" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="eliminar_doc" value="<?= e($d['id']) ?>">
                  <button class="btn btn-danger btn-sm"
                          onclick="return confirmar('¿Eliminar este recurso?')">✕</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Añadir recurso -->
  <div class="card" style="align-self:start">
    <div class="card-title">Añadir recurso</div>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Título</label>
        <input type="text" name="titulo" class="form-control"
               placeholder="Ej: Manual de Apache" required maxlength="150">
      </div>
      <div class="form-group">
        <label>URL (opcional)</label>
        <input type="url" name="url" class="form-control"
               placeholder="https://...">
      </div>
      <div class="form-group">
        <label>Tipo</label>
        <select name="tipo" class="form-control">
          <option value="enlace">🔗 Enlace</option>
          <option value="manual">📘 Manual</option>
          <option value="config">⚙ Configuración</option>
          <option value="nota">📝 Nota</option>
        </select>
      </div>
      <div class="form-group">
        <label>Notas</label>
        <textarea name="notas" class="form-control" rows="3"
                  placeholder="Descripción breve..."></textarea>
      </div>
      <button type="submit" name="nuevo_doc" value="1" class="btn btn-primary" style="width:100%">
        + Añadir recurso
      </button>
    </form>
  </div>

</div>

<?php layout_close(); ?>
