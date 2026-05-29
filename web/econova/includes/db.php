<?php
// ============================================================
// includes/db.php — Conexión PDO (singleton)
// Seguridad: prepared statements, charset utf8mb4, modo estricto
// ============================================================

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    public static function get(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST, DB_NAME, DB_CHARSET
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,   // prepared reales
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // No exponer detalles de conexión al usuario
                error_log('DB connection error: ' . $e->getMessage());
                die('Error de conexión con la base de datos. Inténtalo más tarde.');
            }
        }
        return self::$instance;
    }

    // Evitar clonación / deserialización
    private function __clone() {}
    public function __wakeup() { throw new \Exception('No serializable'); }
}

// Atajo global
function db(): PDO { return Database::get(); }
