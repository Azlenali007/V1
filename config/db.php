<?php
/**
 * Database Connection using PDO (MariaDB / MySQL)
 * SMM Panel
 */

declare(strict_types=1);

class Database {
    private static ?PDO $instance = null;

    private static string $host = '127.0.0.1';
    private static string $port = '3306';
    private static string $dbname = 'smm_panel';
    private static string $user = 'root';
    private static string $pass = '';

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            // Check for environment variables if configured
            $host = getenv('DB_HOST') ?: self::$host;
            $port = getenv('DB_PORT') ?: self::$port;
            $dbname = getenv('DB_NAME') ?: self::$dbname;
            $user = getenv('DB_USER') ?: self::$user;
            $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : self::$pass;

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Return clean JSON error if called via API
                if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Database connection error: ' . $e->getMessage()
                    ]);
                    exit;
                }
                throw $e;
            }
        }

        return self::$instance;
    }
}
