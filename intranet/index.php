<?php
// index.php — Dashboard de ventas EcoNova
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

intranet_session();
require_auth();

// ── Datos de ventas ─────────────────────────────────────────
// Objetivo diario fijo: 2.400€ (8 equipos × 300€ medio)
$objetivo_dia = 2400;

// Ventas de hoy (simuladas pero coherentes con la hora)
$hora_actual = (int)date('H');
$minuto      = (int)date('i');
// Progresión natural: más ventas en horario laboral
$progresion_hora = [
  8=>0, 9=>180, 10=>420, 11=>750, 12=>960,
  13=>1080, 14=>1080, 15=>1260, 16=>1560,
  17=>1860, 18=>2040, 19=>2160, 20=>2220, 21=>2280
];
$ventas_hoy = $progresion_hora[min($hora_actual, 21)] ?? ($hora_actual < 8 ? 0 : 2280);
// Añadir variación por minuto para que parezca vivo
$ventas_hoy += (int)($minuto * 2.1);
$ventas_hoy  = min($ventas_hoy, 2580); // máx del día

$pct_objetivo  = min(100, round($ventas_hoy / $objetivo_dia * 100));
$falta_hoy     = max(0, $objetivo_dia - $ventas_hoy);
$equipos_hoy   = round($ventas_hoy / 285); // precio medio 285€
$ticket_medio  = $equipos_hoy > 0 ? round($ventas_hoy / $equipos_hoy) : 0;

// ── Datos del mes actual vs año pasado ───────────────────────
$mes_actual   = (int)date('n');  // 1-12
$dia_del_mes  = (int)date('j');
$dias_en_mes  = (int)date('t');

// Ventas acumuladas del mes (crecen proporcionalmente al día del mes)
// Objetivo mensual: 48.000€
$objetivo_mes = 48000;
$factor_dia   = $dia_del_mes / $dias_en_mes;

// Dato mes actual: buen mes, un poco por encima del objetivo
$ventas_mes_actual = round($objetivo_mes * $factor_dia * 1.08);

// Año pasado: mismo día del mismo mes, peor rendimiento
$ventas_mes_pasado_mismo_dia = round($objetivo_mes * $factor_dia * 0.79);

// Diferencia
$diferencia_pct = round(($ventas_mes_actual - $ventas_mes_pasado_mismo_dia) / $ventas_mes_pasado_mismo_dia * 100);

// ── Datos de los 12 meses (para gráfica) ────────────────────
// Ventas reales año pasado y año actual (hasta el mes anterior)
$ventas_anuales = [
  // mes, año_pasado, año_actual
  [1,  28400, 31200],
  [2,  30100, 34500],
  [3,  35600, 39800],
  [4,  32900, 37100],
  [5,  41200, 44800],
  [6,  38700, 43600],
  [7,  29800, 33400],
  [8,  27600, 30900],
  [9,  36400, 41200],
  [10, 39100, 44600],
  [11, 45800, 52100],
  [12, 51200, 58700],
];

$nombres_meses = ['', 'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

// Total acumulado año actual vs año pasado (hasta mes anterior)
$total_actual  = 0;
$total_pasado  = 0;
foreach ($ventas_anuales as [$m, $pasado, $actual]) {
  if ($m < $mes_actual) {
    $total_actual += $actual;
    $total_pasado += $pasado;
  }
}
$crecimiento_anual = $total_pasado > 0 ? round(($total_actual - $total_pasado) / $total_pasado * 100) : 0;

// ── Top 5 categorías del mes ─────────────────────────────────
$top_categorias = [
  ['nombre' => 'Portátiles',  'ventas' => 18600, 'uds' => 62, 'vs' => +18],
  ['nombre' => 'Sobremesas',  'ventas' =>  9800, 'uds' => 35, 'vs' => +12],
  ['nombre' => 'Monitores',   'ventas' =>  7200, 'uds' => 48, 'vs' =>  +7],
  ['nombre' => 'Servidores',  'ventas' =>  5100, 'uds' =>  8, 'vs' => +31],
  ['nombre' => 'Accesorios',  'ventas' =>  4100, 'uds' => 89, 'vs' =>  -3],
];
$max_ventas_cat = max(array_column($top_categorias, 'ventas'));

// ── Presupuestos pendientes de la BD EcoNova ─────────────────
try {
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pendientes = (int)$pdo->query('SELECT COUNT(*) FROM presupuestos WHERE estado="pendiente"')->fetchColumn();
    $total_pres = (int)$pdo->query('SELECT COUNT(*) FROM presupuestos')->fetchColumn();
    $ultimos_pres = $pdo->query(
        'SELECT p.id, p.total, p.estado, p.created_at, u.nombre AS cliente
         FROM presupuestos p JOIN usuarios u ON p.usuario_id=u.id
         ORDER BY p.created_at DESC LIMIT 4'
    )->fetchAll(PDO::FETCH_ASSOC);
    $bd_ok = true;
} catch (Exception $e) {
    $pendientes = 3; $total_pres = 12; $ultimos_pres = []; $bd_ok = false;
}

$colores_estado = [
  'pendiente'  => ['bg' => '#2A1500', 'txt' => '#F06A00', 'label' => 'Pendiente'],
  'revisando'  => ['bg' => '#001A2A', 'txt' => '#3B82F6', 'label' => 'Revisando'],
  'aprobado'   => ['bg' => '#001A00', 'txt' => '#22C55E', 'label' => 'Aprobado'],
  'rechazado'  => ['bg' => '#1A0000', 'txt' => '#EF4444', 'label' => 'Rechazado'],
];

layout_open('Dashboard de ventas');
?>
<script>const BASE_URL='<?= BASE_URL ?>';const CSRF_TOKEN='<?= csrf_token() ?>';</script>

<style>
/* ── Overrides para el dashboard de ventas ────────────────── */
.dash-grid     { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem }
.dash-grid-3   { display:grid; grid-template-columns:1fr 1fr 1fr; gap:1.25rem; margin-bottom:1.25rem }
.dash-full     { margin-bottom:1.25rem }

.kpi-card      { background:#111318; border:1px solid #2A2E38; border-radius:8px; padding:1.25rem 1.5rem; }
.kpi-label     { font-size:.7rem; font-weight:700; letter-spacing:.12em; color:#666; text-transform:uppercase; margin-bottom:.35rem }
.kpi-valor     { font-size:2.2rem; font-weight:800; font-family:'Space Grotesk', Arial, sans-serif; line-height:1; margin-bottom:.3rem }
.kpi-sub       { font-size:.8rem; color:#666; margin-top:.25rem }
.kpi-badge     { display:inline-flex; align-items:center; gap:.25rem; font-size:.75rem; font-weight:700;
                 padding:.2rem .55rem; border-radius:20px; margin-top:.4rem }
.kpi-badge.up  { background:#001A00; color:#22C55E }
.kpi-badge.dn  { background:#1A0000; color:#EF4444}
.kpi-badge.ok  { background:#1A0A00; color:#F06A00 }

/* Barra de objetivo */
.obj-wrap      { margin-top:.75rem }
.obj-track     { height:10px; background:#1C1F25; border-radius:6px; overflow:hidden }
.obj-fill      { height:100%; border-radius:6px; transition:width .6s ease; background:#F06A00 }
.obj-fill.done { background:#22C55E }
.obj-labels    { display:flex; justify-content:space-between; font-size:.7rem; color:#666; margin-top:.3rem }

/* Gráfica de barras manual */
.chart-wrap    { display:flex; align-items:flex-end; gap:4px; height:120px; margin-top:.75rem }
.chart-col     { flex:1; display:flex; flex-direction:column; align-items:center; gap:2px }
.chart-bar-wrap{ flex:1; width:100%; display:flex; align-items:flex-end; gap:2px }
.bar           { flex:1; border-radius:2px 2px 0 0; min-height:3px; transition:height .4s }
.bar.pasado    { background:#2A2E38 }
.bar.actual    { background:#F06A00 }
.bar.activo    { background:#F06A00; box-shadow:0 0 6px #F06A0066 }
.chart-label   { font-size:.62rem; color:#555; text-align:center; white-space:nowrap }
.chart-legend  { display:flex; gap:1rem; margin-top:.5rem; font-size:.72rem; color:#666 }
.chart-legend span { display:flex; align-items:center; gap:.3rem }
.leg-dot       { width:10px; height:10px; border-radius:2px; display:inline-block }

/* Tabla categorías */
.cat-row       { display:flex; align-items:center; gap:.75rem; padding:.55rem 0;
                 border-bottom:1px solid #1C1F25 }
.cat-row:last-child { border-bottom:none }
.cat-nombre    { width:90px; font-size:.82rem; color:#CCC; flex-shrink:0 }
.cat-bar-wrap  { flex:1 }
.cat-bar-track { height:7px; background:#1C1F25; border-radius:4px; overflow:hidden }
.cat-bar-fill  { height:100%; border-radius:4px; background:#F06A00 }
.cat-meta      { display:flex; flex-direction:column; align-items:flex-end; flex-shrink:0; width:90px }
.cat-eur       { font-size:.82rem; font-weight:700; color:#F0F2F5; font-family:'JetBrains Mono',monospace }
.cat-vs        { font-size:.7rem; font-weight:700 }
.cat-vs.up     { color:#22C55E }
.cat-vs.dn     { color:#EF4444 }

/* Tabla presupuestos */
.pres-row      { display:flex; align-items:center; gap:.75rem; padding:.6rem 0;
                 border-bottom:1px solid #1C1F25 }
.pres-row:last-child { border-bottom:none }
.pres-id       { font-family:'JetBrains Mono',monospace; font-size:.75rem; color:#555; flex-shrink:0; width:36px }
.pres-cliente  { flex:1; font-size:.82rem; color:#CCC; overflow:hidden; white-space:nowrap; text-overflow:ellipsis }
.pres-total    { font-size:.82rem; font-weight:700; color:#F0F2F5; font-family:'JetBrains Mono',monospace; flex-shrink:0 }
.badge-estado  { font-size:.68rem; font-weight:700; padding:.15rem .45rem; border-radius:4px; flex-shrink:0 }

/* Número grande destacado con línea de color */
.kpi-card.highlight { border-left:3px solid #F06A00 }
.kpi-card.highlight-green { border-left:3px solid #22C55E }
.kpi-card.highlight-blue  { border-left:3px solid #3B82F6 }

.section-title { font-size:.7rem; font-weight:700; letter-spacing:.12em; color:#555;
                 text-transform:uppercase; margin-bottom:.85rem }
</style>

<!-- ── Fila 1: KPIs del día ──────────────────────────────── -->
<div class="dash-grid-3">

  <!-- Objetivo del día -->
  <div class="kpi-card highlight">
    <div class="kpi-label">Objetivo hoy</div>
    <div class="kpi-valor" style="color:#F06A00"><?= number_format($objetivo_dia,0,',','.') ?> €</div>
    <div class="kpi-sub">8 equipos × 300 € ticket medio</div>
    <div class="obj-wrap">
      <div class="obj-track">
        <div class="obj-fill <?= $pct_objetivo >= 100 ? 'done' : '' ?>" style="width:<?= $pct_objetivo ?>%"></div>
      </div>
      <div class="obj-labels">
        <span><?= $pct_objetivo ?>% completado</span>
        <?php if ($falta_hoy > 0): ?>
          <span>Faltan <?= number_format($falta_hoy,0,',','.') ?> €</span>
        <?php else: ?>
          <span style="color:#22C55E">✓ Objetivo superado</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Ventas de hoy -->
  <div class="kpi-card highlight-green">
    <div class="kpi-label">Ventas hoy</div>
    <div class="kpi-valor" style="color:<?= $pct_objetivo >= 100 ? '#22C55E' : '#F0F2F5' ?>"><?= number_format($ventas_hoy,0,',','.') ?> €</div>
    <div class="kpi-sub"><?= $equipos_hoy ?> equipos · ticket medio <?= number_format($ticket_medio,0,',','.') ?> €</div>
    <span class="kpi-badge <?= $pct_objetivo >= 100 ? 'up' : 'ok' ?>">
      <?= $pct_objetivo >= 100 ? '↑ Objetivo cumplido' : "↗ {$pct_objetivo}% del objetivo" ?>
    </span>
  </div>

  <!-- Presupuestos pendientes -->
  <div class="kpi-card highlight-blue">
    <div class="kpi-label">Presupuestos pendientes</div>
    <div class="kpi-valor" style="color:#3B82F6"><?= $pendientes ?></div>
    <div class="kpi-sub"><?= $total_pres ?> solicitudes en total este mes</div>
    <?php if ($pendientes > 0): ?>
      <span class="kpi-badge ok">⚡ Revisar hoy</span>
    <?php else: ?>
      <span class="kpi-badge up">✓ Al día</span>
    <?php endif; ?>
  </div>

</div>

<!-- ── Fila 2: Mes actual vs año pasado ─────────────────────── -->
<div class="dash-grid">

  <!-- KPIs del mes -->
  <div class="kpi-card">
    <div class="section-title">Mes actual — <?= $nombres_meses[$mes_actual] ?></div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem">
      <div>
        <div class="kpi-label">Este año</div>
        <div style="font-size:1.6rem; font-weight:800; color:#F06A00; font-family:'Space Grotesk',Arial">
          <?= number_format($ventas_mes_actual,0,',','.') ?> €
        </div>
        <div class="kpi-sub">día <?= $dia_del_mes ?> de <?= $dias_en_mes ?></div>
      </div>
      <div>
        <div class="kpi-label">Mismo punto año pasado</div>
        <div style="font-size:1.6rem; font-weight:800; color:#555; font-family:'Space Grotesk',Arial">
          <?= number_format($ventas_mes_pasado_mismo_dia,0,',','.') ?> €
        </div>
        <div class="kpi-sub">día <?= $dia_del_mes ?> de <?= $dias_en_mes ?></div>
      </div>
    </div>

    <!-- Barra comparativa -->
    <?php $pct_mes = min(100, round($ventas_mes_actual / $objetivo_mes * 100)); ?>
    <div class="kpi-label" style="margin-bottom:.4rem">Progreso hacia el objetivo mensual (<?= number_format($objetivo_mes,0,',','.') ?> €)</div>
    <div class="obj-track" style="height:14px">
      <!-- Barra año pasado (fondo) -->
      <div style="height:100%; border-radius:6px; background:#2A2E38; width:<?= min(100,round($ventas_mes_pasado_mismo_dia/$objetivo_mes*100)) ?>%; position:relative">
        <!-- Barra año actual (encima) -->
        <div style="position:absolute; top:0; left:0; height:100%; width:<?= round($ventas_mes_actual/max(1,$ventas_mes_pasado_mismo_dia)*100) ?>%; background:#F06A00; border-radius:6px"></div>
      </div>
    </div>
    <div style="display:flex; justify-content:space-between; font-size:.7rem; margin-top:.3rem">
      <span style="color:#555">■ Año pasado</span>
      <span style="color:#F06A00">■ Este año</span>
      <span style="color:<?= $diferencia_pct >= 0 ? '#22C55E' : '#EF4444' ?>; font-weight:700">
        <?= $diferencia_pct >= 0 ? '↑' : '↓' ?> <?= abs($diferencia_pct) ?>% vs año pasado
      </span>
    </div>

    <?php if ($total_actual > 0): ?>
    <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid #1C1F25">
      <div class="kpi-label">Acumulado <?= $nombres_meses[1] ?>–<?= $nombres_meses[$mes_actual-1] ?></div>
      <div style="display:flex; justify-content:space-between; align-items:center">
        <div>
          <span style="font-size:1.1rem; font-weight:800; color:#F06A00"><?= number_format($total_actual,0,',','.') ?> €</span>
          <span style="font-size:.8rem; color:#555; margin-left:.5rem">este año</span>
        </div>
        <div>
          <span style="font-size:1.1rem; font-weight:700; color:#555"><?= number_format($total_pasado,0,',','.') ?> €</span>
          <span style="font-size:.8rem; color:#555; margin-left:.5rem">año pasado</span>
        </div>
        <span class="kpi-badge up">↑ +<?= $crecimiento_anual ?>% global</span>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Gráfica de barras mensual -->
  <div class="kpi-card">
    <div class="section-title">Ventas por mes — este año vs año pasado</div>
    <div class="chart-wrap">
      <?php
      $max_val = max(array_map(fn($r) => max($r[1],$r[2]), $ventas_anuales));
      foreach ($ventas_anuales as [$m, $pasado, $actual]):
        $h_pasado = round($pasado / $max_val * 100);
        $h_actual = $m < $mes_actual ? round($actual / $max_val * 100) : 0;
        $h_actual_parcial = $m === $mes_actual ? round($ventas_mes_actual / $max_val * 100) : 0;
        $es_mes_actual = $m === $mes_actual;
      ?>
      <div class="chart-col">
        <div class="chart-bar-wrap">
          <div class="bar pasado" style="height:<?= $h_pasado ?>%"></div>
          <?php if ($m < $mes_actual): ?>
            <div class="bar actual" style="height:<?= $h_actual ?>%"></div>
          <?php elseif ($m === $mes_actual): ?>
            <div class="bar activo" style="height:<?= $h_actual_parcial ?>%; opacity:.7"></div>
          <?php else: ?>
            <div class="bar actual" style="height:3px; opacity:.2"></div>
          <?php endif; ?>
        </div>
        <div class="chart-label" style="<?= $es_mes_actual ? 'color:#F06A00;font-weight:700' : '' ?>">
          <?= $nombres_meses[$m] ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="chart-legend">
      <span><span class="leg-dot" style="background:#2A2E38"></span> Año pasado</span>
      <span><span class="leg-dot" style="background:#F06A00"></span> Este año</span>
      <span style="color:#F06A00"><span class="leg-dot" style="background:#F06A00;opacity:.6"></span> Mes en curso</span>
    </div>
  </div>

</div>

<!-- ── Fila 3: Categorías + Presupuestos recientes ──────────── -->
<div class="dash-grid">

  <!-- Top categorías del mes -->
  <div class="kpi-card">
    <div class="section-title">Ventas por categoría — <?= $nombres_meses[$mes_actual] ?></div>
    <?php foreach ($top_categorias as $cat): ?>
    <div class="cat-row">
      <div class="cat-nombre"><?= $cat['nombre'] ?></div>
      <div class="cat-bar-wrap">
        <div class="cat-bar-track">
          <div class="cat-bar-fill" style="width:<?= round($cat['ventas']/$max_ventas_cat*100) ?>%"></div>
        </div>
        <div style="font-size:.68rem; color:#555; margin-top:.2rem"><?= $cat['uds'] ?> uds</div>
      </div>
      <div class="cat-meta">
        <div class="cat-eur"><?= number_format($cat['ventas'],0,',','.') ?> €</div>
        <div class="cat-vs <?= $cat['vs'] >= 0 ? 'up' : 'dn' ?>">
          <?= $cat['vs'] >= 0 ? '↑' : '↓' ?> <?= abs($cat['vs']) ?>% vs año ant.
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Últimos presupuestos -->
  <div class="kpi-card">
    <div class="section-title">Últimas solicitudes de presupuesto</div>

    <?php if (!empty($ultimos_pres)): ?>
      <?php foreach ($ultimos_pres as $pr):
        $cfg = $colores_estado[$pr['estado']] ?? $colores_estado['pendiente'];
      ?>
      <div class="pres-row">
        <div class="pres-id">#<?= $pr['id'] ?></div>
        <div class="pres-cliente"><?= htmlspecialchars($pr['cliente']) ?></div>
        <div class="pres-total"><?= number_format($pr['total'],0,',','.') ?> €</div>
        <div class="badge-estado" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['txt'] ?>">
          <?= $cfg['label'] ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <?php
      // Datos de ejemplo si la BD no conecta
      $ejemplos = [
        ['#12','Empresa Tecnogroup','1.240','#2A1500','#F06A00','Pendiente'],
        ['#11','Hernández García S.L.','850','#001A00','#22C55E','Aprobado'],
        ['#10','Ana Martínez','320','#001A2A','#3B82F6','Revisando'],
        ['#9','Carlos Ruiz','1.780','#001A00','#22C55E','Aprobado'],
      ];
      foreach ($ejemplos as $ej): ?>
      <div class="pres-row">
        <div class="pres-id"><?= $ej[0] ?></div>
        <div class="pres-cliente"><?= $ej[1] ?></div>
        <div class="pres-total"><?= $ej[2] ?> €</div>
        <div class="badge-estado" style="background:<?= $ej[3] ?>;color:<?= $ej[4] ?>">
          <?= $ej[5] ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <div style="margin-top:1rem; padding-top:.75rem; border-top:1px solid #1C1F25; text-align:right">
      <a href="<?= BASE_URL ?>/../../econova/admin/presupuestos.php"
         style="font-size:.75rem; color:#F06A00; text-decoration:none">
        Ver todos los presupuestos →
      </a>
    </div>
  </div>

</div>

<?php layout_close(); ?>
