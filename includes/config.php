<?php
// ============================================================
// includes/config.php — Configuración global de EcoNova
// ============================================================

// ── Entorno ─────────────────────────────────────────────────
define('ENV', 'development'); // 'development' | 'production'
define('BASE_URL', 'http://localhost/econova');
define('SITE_NAME', 'EcoNova');

// ── Base de datos ────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'econova');
define('DB_USER', 'root');        // Cambiar en producción
define('DB_PASS', '');            // Cambiar en producción
define('DB_CHARSET', 'utf8mb4');

// ── Seguridad ────────────────────────────────────────────────
define('BCRYPT_COST', 12);
define('SESSION_LIFETIME', 3600);          // 1 hora
define('CSRF_TOKEN_LENGTH', 32);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);         // 15 minutos

// ── Paginación ───────────────────────────────────────────────
define('PRODUCTOS_POR_PAGINA', 12);

// ── Errores ──────────────────────────────────────────────────
if (ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
