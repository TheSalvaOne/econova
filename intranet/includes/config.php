<?php
// ============================================================
// includes/config.php — Intranet EcoNova
// Accesible solo en red interna + Tailscale (puerto 8080)
// ============================================================

define('SITE_NAME',  'EcoNova Intranet');
define('BASE_URL',   'http://intranet.econova.local');   // ajustar a IP/hostname real
define('ENV',        'development');

// ── Base de datos (misma instancia MySQL que la web pública) ─
define('DB_HOST',    'localhost');
define('DB_NAME',    'econova');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ── Seguridad intranet ───────────────────────────────────────
define('INTRANET_USER', 'admin');                         // usuario básico HTTP
define('INTRANET_PASS', 'HASH-BCRYPT-AQUI'); // Generar con: php -r "echo password_hash('tupassword', PASSWORD_BCRYPT);" // bcrypt
// Para regenerar: php -r "echo password_hash('TuPassword123!', PASSWORD_BCRYPT, ['cost'=>12]);"

define('SESSION_LIFETIME', 3600);
define('CSRF_TOKEN_LENGTH', 32);

// ── Rutas de servicios monitorizados ────────────────────────
// Ajustar IPs según la red real desplegada en Proxmox
define('VM_WEB_IP',    '192.168.1.101');  // VM1 — servidor web
define('VM_SERV_IP',   '192.168.1.102');  // VM2 — DNS + FTP
define('FTP_HOST',     '192.168.1.102');
define('FTP_PORT',     21);

if (ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
