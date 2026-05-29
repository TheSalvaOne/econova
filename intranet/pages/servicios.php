<?php
// pages/servicios.php — Estado detallado de todos los servicios
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

intranet_session();
require_auth();

// Comprobar servicios con tiempo de respuesta
function check_service(string $host, int $port, float $timeout = 2): array {
    $start = microtime(true);
    $sock  = @fsockopen($host, $port, $errno, $errstr, $timeout);
    $ms    = round((microtime(true) - $start) * 1000, 1);
    $up    = (bool)$sock;
    if ($sock) fclose($sock);
    return ['up' => $up, 'ms' => $ms, 'error' => $errstr];
}

$checks = [
    ['nombre'=>'Apache (HTTP)',      'host'=>VM_WEB_IP,  'puerto'=>80,   'vm'=>'VM1 — Servidor Web',    'desc'=>'Servidor web principal de EcoNova'],
    ['nombre'=>'MySQL',              'host'=>VM_WEB_IP,  'puerto'=>3306, 'vm'=>'VM1 — Servidor Web',    'desc'=>'Base de datos de la plataforma EcoNova'],
    ['nombre'=>'SSH VM1',            'host'=>VM_WEB_IP,  'puerto'=>22,   'vm'=>'VM1 — Servidor Web',    'desc'=>'Acceso remoto administración VM1'],
    ['nombre'=>'DNS BIND9 (TCP)',    'host'=>VM_SERV_IP, 'puerto'=>53,   'vm'=>'VM2 — Servicios Red',   'desc'=>'Resolución de nombres econova.local'],
    ['nombre'=>'FTP vsftpd',         'host'=>VM_SERV_IP, 'puerto'=>21,   'vm'=>'VM2 — Servicios Red',   'desc'=>'Transferencia de ficheros entre VMs'],
    ['nombre'=>'SSH VM2',            'host'=>VM_SERV_IP, 'puerto'=>22,   'vm'=>'VM2 — Servicios Red',   'desc'=>'Acceso remoto administración VM2'],
];

foreach ($checks as &$c) {
    $r = check_service($c['host'], $c['puerto']);
    $c['up']    = $r['up'];
    $c['ms']    = $r['ms'];
    $c['error'] = $r['error'];
}
unset($c);

$up_count   = count(array_filter($checks, fn($c) => $c['up']));
$down_count = count($checks) - $up_count;

layout_open('Servicios en red');
?>

<meta name="csrf" content="<?= csrf_token() ?>">

<!-- Resumen rápido -->
<div class="grid-3" style="margin-bottom:1.5rem">
  <div class="stat-card">
    <span class="stat-label">Servicios activos</span>
    <span class="stat-value verde"><?= $up_count ?> / <?= count($checks) ?></span>
  </div>
  <div class="stat-card">
    <span class="stat-label">Servicios caídos</span>
    <span class="stat-value <?= $down_count > 0 ? 'rojo' : 'verde' ?>"><?= $down_count ?></span>
  </div>
  <div class="stat-card">
    <span class="stat-label">Última comprobación</span>
    <span class="stat-value" style="font-size:1rem; line-height:1.4"><?= date('H:i:s') ?></span>
  </div>
</div>

<!-- Tabla detallada de servicios -->
<div class="card">
  <div class="section-header">
    <span class="card-title">Comprobación de puertos</span>
    <a href="servicios.php" class="btn btn-ghost btn-sm">↺ Actualizar</a>
  </div>

  <table class="tabla">
    <thead>
      <tr>
        <th>Estado</th>
        <th>Servicio</th>
        <th>VM / Rol</th>
        <th>Host : Puerto</th>
        <th>Latencia</th>
        <th>Descripción</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($checks as $c): ?>
        <tr>
          <td>
            <span class="badge-estado <?= $c['up'] ? 'badge-up' : 'badge-down' ?>">
              <?= $c['up'] ? '▲ UP' : '▼ DOWN' ?>
            </span>
          </td>
          <td style="font-weight:600; color:var(--txt-1)"><?= e($c['nombre']) ?></td>
          <td class="mono"><?= e($c['vm']) ?></td>
          <td class="mono"><?= e($c['host']) ?>:<strong><?= $c['puerto'] ?></strong></td>
          <td class="mono" style="color:<?= $c['up'] ? ($c['ms'] < 10 ? 'var(--verde)' : 'var(--amarillo)') : 'var(--rojo)' ?>">
            <?= $c['up'] ? $c['ms'] . ' ms' : '—' ?>
          </td>
          <td style="color:var(--txt-3)"><?= e($c['desc']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Comandos útiles -->
<div class="card" style="margin-top:1rem">
  <div class="card-title">Referencia rápida — comandos de administración</div>
  <div class="grid-2" style="margin-top:.5rem">
    <div>
      <p style="font-size:.72rem; color:var(--txt-3); font-family:var(--font-mono); margin-bottom:.5rem">VM1 — Apache / MySQL</p>
      <div class="terminal"><span class="prompt">$ </span>sudo systemctl status apache2
<span class="prompt">$ </span>sudo systemctl restart apache2
<span class="prompt">$ </span>sudo systemctl status mysql
<span class="prompt">$ </span>sudo tail -f /var/log/apache2/error.log</div>
    </div>
    <div>
      <p style="font-size:.72rem; color:var(--txt-3); font-family:var(--font-mono); margin-bottom:.5rem">VM2 — DNS / FTP</p>
      <div class="terminal"><span class="prompt">$ </span>sudo systemctl status bind9
<span class="prompt">$ </span>sudo systemctl restart bind9
<span class="prompt">$ </span>sudo systemctl status vsftpd
<span class="prompt">$ </span>sudo tail -f /var/log/vsftpd.log</div>
    </div>
  </div>
</div>

<?php layout_close(); ?>
