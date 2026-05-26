<?php
// ============================================================
// includes/security.php — Funciones de seguridad
// Cubre: CSRF, sanitización, autenticación, rate limiting, audit
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// ── Sesión segura ────────────────────────────────────────────
function iniciar_sesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'domain'   => '',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        // Regenerar ID periódicamente (previene session fixation)
        if (!isset($_SESSION['_last_regen'])) {
            $_SESSION['_last_regen'] = time();
        } elseif (time() - $_SESSION['_last_regen'] > 300) {
            session_regenerate_id(true);
            $_SESSION['_last_regen'] = time();
        }
    }
}

// ── CSRF ─────────────────────────────────────────────────────
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('Token CSRF inválido.');
    }
}

// ── Sanitización de salida ───────────────────────────────────
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize_int(mixed $val): int {
    return (int) filter_var($val, FILTER_SANITIZE_NUMBER_INT);
}

function sanitize_email(string $email): string|false {
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}

// ── Autenticación ────────────────────────────────────────────
function usuario_logueado(): bool {
    return !empty($_SESSION['usuario_id']);
}

function usuario_admin(): bool {
    return usuario_logueado() && ($_SESSION['usuario_rol'] ?? '') === 'admin';
}

function require_login(): void {
    if (!usuario_logueado()) {
        header('Location: ' . BASE_URL . '/pages/login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function require_admin(): void {
    if (!usuario_admin()) {
        http_response_code(403);
        die('Acceso denegado.');
    }
}

function login_usuario(array $usuario): void {
    session_regenerate_id(true);
    $_SESSION['usuario_id']     = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_email']  = $usuario['email'];
    $_SESSION['usuario_rol']    = $usuario['rol'];
    $_SESSION['_last_regen']    = time();
}

function logout_usuario(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ── Rate limiting para login (en sesión, válido para demo) ───
function check_rate_limit(string $key): bool {
    $attempts_key = 'rl_attempts_' . $key;
    $time_key     = 'rl_time_' . $key;

    $attempts = $_SESSION[$attempts_key] ?? 0;
    $first    = $_SESSION[$time_key] ?? time();

    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        if (time() - $first < LOGIN_LOCKOUT_TIME) {
            return false; // bloqueado
        }
        // Reset tras lockout
        $_SESSION[$attempts_key] = 0;
        $_SESSION[$time_key]     = time();
    }
    return true;
}

function increment_rate_limit(string $key): void {
    $attempts_key = 'rl_attempts_' . $key;
    $time_key     = 'rl_time_' . $key;
    if (!isset($_SESSION[$time_key])) $_SESSION[$time_key] = time();
    $_SESSION[$attempts_key] = ($_SESSION[$attempts_key] ?? 0) + 1;
}

function reset_rate_limit(string $key): void {
    unset($_SESSION['rl_attempts_' . $key], $_SESSION['rl_time_' . $key]);
}

// ── Audit log ────────────────────────────────────────────────
function audit(string $accion, ?string $tabla = null, ?int $registro_id = null): void {
    try {
        $stmt = db()->prepare(
            'INSERT INTO audit_log (usuario_id, accion, tabla, registro_id, ip, user_agent)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $_SESSION['usuario_id'] ?? null,
            $accion,
            $tabla,
            $registro_id,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    } catch (PDOException) {
        // Silencioso: el log no debe romper el flujo principal
    }
}

// ── Headers de seguridad HTTP ────────────────────────────────
function security_headers(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; "
         . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
         . "font-src 'self' https://fonts.gstatic.com; "
         . "script-src 'self' 'unsafe-inline'; "
         . "img-src 'self' data:;");
}
