<?php
// includes/auth.php — Autenticación y seguridad de la intranet
require_once __DIR__ . '/config.php';

function intranet_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => isset($_SERVER['HTTPS']),
        ]);
        session_start();
        // Regenerar cada 5 min — previene session fixation
        if (!isset($_SESSION['_regen'])) {
            $_SESSION['_regen'] = time();
        } elseif (time() - $_SESSION['_regen'] > 300) {
            session_regenerate_id(true);
            $_SESSION['_regen'] = time();
        }
    }
}

function logueado(): bool {
    return !empty($_SESSION['intranet_auth']);
}

function require_auth(): void {
    if (!logueado()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_verify(): void {
    if (!hash_equals(csrf_token(), $_POST['csrf'] ?? '')) {
        http_response_code(403); die('CSRF inválido.');
    }
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ── Ping a host (comprueba si una IP responde) ───────────────
function host_ping(string $ip, int $timeout = 1): bool {
    $sock = @fsockopen($ip, 80, $errno, $errstr, $timeout);
    if ($sock) { fclose($sock); return true; }
    // Fallback: intentar puerto 22 (SSH)
    $sock = @fsockopen($ip, 22, $errno, $errstr, $timeout);
    if ($sock) { fclose($sock); return true; }
    return false;
}

// ── Leer métricas del servidor local (VM donde corre la intranet)
function server_metrics(): array {
    $metrics = [];

    // CPU — leer /proc/stat (Linux)
    if (file_exists('/proc/stat')) {
        $stat1 = file_get_contents('/proc/stat');
        usleep(200000); // 200ms de muestra
        $stat2 = file_get_contents('/proc/stat');
        $cpu1  = array_map('intval', array_slice(preg_split('/\s+/', explode("\n", $stat1)[0]), 1));
        $cpu2  = array_map('intval', array_slice(preg_split('/\s+/', explode("\n", $stat2)[0]), 1));
        $idle1 = $cpu1[3]; $total1 = array_sum($cpu1);
        $idle2 = $cpu2[3]; $total2 = array_sum($cpu2);
        $diff_total = $total2 - $total1;
        $diff_idle  = $idle2  - $idle1;
        $metrics['cpu'] = $diff_total > 0
            ? round((($diff_total - $diff_idle) / $diff_total) * 100, 1)
            : 0;
    } else {
        $metrics['cpu'] = null;
    }

    // RAM — leer /proc/meminfo
    if (file_exists('/proc/meminfo')) {
        $meminfo = [];
        foreach (file('/proc/meminfo') as $line) {
            if (preg_match('/^(\w+):\s+(\d+)/', $line, $m)) {
                $meminfo[$m[1]] = (int)$m[2];
            }
        }
        $total     = $meminfo['MemTotal']     ?? 0;
        $available = $meminfo['MemAvailable'] ?? 0;
        $used      = $total - $available;
        $metrics['ram_total'] = round($total / 1024);     // MB
        $metrics['ram_used']  = round($used  / 1024);     // MB
        $metrics['ram_pct']   = $total > 0 ? round(($used / $total) * 100, 1) : 0;
    } else {
        $metrics['ram_total'] = $metrics['ram_used'] = $metrics['ram_pct'] = null;
    }

    // Disco — df en el root
    $df = @disk_free_space('/');
    $dt = @disk_total_space('/');
    if ($df !== false && $dt !== false) {
        $metrics['disk_total'] = round($dt / (1024**3), 1); // GB
        $metrics['disk_used']  = round(($dt - $df) / (1024**3), 1);
        $metrics['disk_pct']   = round((($dt - $df) / $dt) * 100, 1);
    } else {
        $metrics['disk_total'] = $metrics['disk_used'] = $metrics['disk_pct'] = null;
    }

    // Uptime
    if (file_exists('/proc/uptime')) {
        $up = (float)explode(' ', file_get_contents('/proc/uptime'))[0];
        $d  = floor($up / 86400);
        $h  = floor(($up % 86400) / 3600);
        $m  = floor(($up % 3600)  / 60);
        $metrics['uptime'] = "{$d}d {$h}h {$m}m";
    } else {
        $metrics['uptime'] = null;
    }

    return $metrics;
}
