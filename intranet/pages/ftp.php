<?php
// pages/ftp.php — Explorador de archivos FTP
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

intranet_session();
require_auth();

// Credenciales FTP (en producción: usar variables de entorno)
define('FTP_USER', 'ftpuser');
define('FTP_PASS', 'ftppass123');   // ← cambiar en producción

$path    = $_GET['path'] ?? '/';
$path    = '/' . trim(str_replace(['..', "\0"], '', $path), '/'); // sanitizar
$msg     = '';
$msg_type = 'ok';
$files   = [];
$ftp_ok  = false;

// ── Conectar a FTP ───────────────────────────────────────────
$ftp = @ftp_connect(FTP_HOST, FTP_PORT, 5);
if ($ftp) {
    if (@ftp_login($ftp, FTP_USER, FTP_PASS)) {
        ftp_pasv($ftp, true); // modo pasivo (necesario detrás de NAT)
        $ftp_ok = true;

        // Subir fichero
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichero'])) {
            csrf_verify();
            $tmp  = $_FILES['fichero']['tmp_name'];
            $name = basename($_FILES['fichero']['name']);
            $name = preg_replace('/[^a-zA-Z0-9._\-]/', '_', $name);
            $dest = rtrim($path, '/') . '/' . $name;
            if (@ftp_put($ftp, $dest, $tmp, FTP_BINARY)) {
                $msg = "Fichero '$name' subido correctamente.";
            } else {
                $msg = "Error al subir el fichero."; $msg_type = 'error';
            }
        }

        // Eliminar fichero
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
            csrf_verify();
            $target = $path . '/' . basename($_POST['eliminar']);
            if (@ftp_delete($ftp, $target)) {
                $msg = "Fichero eliminado.";
            } else {
                $msg = "No se pudo eliminar."; $msg_type = 'error';
            }
        }

        // Listar directorio
        $raw = @ftp_rawlist($ftp, $path);
        if ($raw !== false) {
            foreach ($raw as $line) {
                // Parsear línea: drwxr-xr-x 2 user group 4096 Jan 1 12:00 nombre
                if (preg_match('/^([\-drwx]+)\s+\d+\s+\S+\s+\S+\s+(\d+)\s+(\S+\s+\S+\s+\S+)\s+(.+)$/', $line, $m)) {
                    $files[] = [
                        'perms' => $m[1],
                        'size'  => (int)$m[2],
                        'date'  => $m[3],
                        'name'  => $m[4],
                        'is_dir'=> str_starts_with($m[1], 'd'),
                    ];
                }
            }
            // Directorios primero
            usort($files, fn($a, $b) => $b['is_dir'] <=> $a['is_dir'] ?: strcmp($a['name'], $b['name']));
        }
    }
    ftp_close($ftp);
}

// Ruta padre
$parent = dirname($path) ?: '/';

function fmt_size(int $bytes): string {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes/1024, 1) . ' KB';
    return round($bytes/1048576, 1) . ' MB';
}

layout_open('Archivos FTP');
?>

<meta name="csrf" content="<?= csrf_token() ?>">

<?php if (!$ftp_ok): ?>
  <div class="alert alert-error">
    No se puede conectar al servidor FTP (<?= FTP_HOST ?>:<?= FTP_PORT ?>).
    Verifica que vsftpd esté activo en la VM2.
  </div>
<?php else: ?>

<?php if ($msg): ?>
  <div class="alert alert-<?= $msg_type === 'error' ? 'error' : 'ok' ?>"><?= e($msg) ?></div>
<?php endif; ?>

<!-- Ruta actual -->
<div class="card" style="margin-bottom:1rem">
  <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem">
    <div>
      <span style="font-family:var(--font-mono); font-size:.78rem; color:var(--txt-3)">Ruta actual: </span>
      <span style="font-family:var(--font-mono); font-size:.83rem; color:var(--naranja)"><?= e($path) ?></span>
    </div>
    <?php if ($path !== '/'): ?>
      <a href="?path=<?= urlencode($parent) ?>" class="btn btn-ghost btn-sm">↑ Subir nivel</a>
    <?php endif; ?>
  </div>
</div>

<!-- Listado de ficheros -->
<div class="card" style="margin-bottom:1rem">
  <div class="card-title">Contenido del directorio</div>

  <?php if (empty($files)): ?>
    <p style="color:var(--txt-3); font-size:.83rem; padding:1rem 0">Directorio vacío.</p>
  <?php else: ?>
    <?php foreach ($files as $f): if ($f['name'] === '.' || $f['name'] === '..') continue; ?>
      <div class="file-row">
        <span class="file-icon"><?= $f['is_dir'] ? '📁' : '📄' ?></span>
        <span class="file-name">
          <?php if ($f['is_dir']): ?>
            <a href="?path=<?= urlencode(rtrim($path,'/').'/'.$f['name']) ?>"
               style="color:var(--naranja)"><?= e($f['name']) ?></a>
          <?php else: ?>
            <?= e($f['name']) ?>
          <?php endif; ?>
        </span>
        <span class="file-size"><?= $f['is_dir'] ? '—' : fmt_size($f['size']) ?></span>
        <span class="file-date"><?= e($f['date']) ?></span>
        <?php if (!$f['is_dir']): ?>
          <form method="POST" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="eliminar" value="<?= e($f['name']) ?>">
            <button class="btn btn-danger btn-sm"
                    onclick="return confirmar('¿Eliminar <?= e($f['name']) ?>?')">✕</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Subir fichero -->
<div class="card">
  <div class="card-title">Subir fichero a <?= e($path) ?></div>
  <form method="POST" enctype="multipart/form-data" style="display:flex; gap:.75rem; align-items:flex-end; flex-wrap:wrap">
    <?= csrf_field() ?>
    <div class="form-group" style="flex:1; margin-bottom:0">
      <label>Fichero</label>
      <input type="file" name="fichero" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Subir →</button>
  </form>
</div>

<?php endif; ?>
<?php layout_close(); ?>
