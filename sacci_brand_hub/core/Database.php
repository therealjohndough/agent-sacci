<?php

namespace Core;

use PDO;
use PDOException;
use Config;

/**
 * Database connection handler using PDO.
 */
class Database
{
    private static ?PDO $connection = null;

    /**
     * Initialize the PDO connection.
     */
    public static function init(): void
    {
        if (self::$connection !== null) {
            return;
        }
        // Load .env if not already loaded
        Config\loadEnv(dirname(__DIR__) . '/.env');
        $host = Config\env('DB_HOST', 'localhost');
        $port = Config\env('DB_PORT', '3306');
        $db   = Config\env('DB_DATABASE', 'sacci_brand_hub');
        $user = Config\env('DB_USERNAME', 'root');
        $pass = Config\env('DB_PASSWORD', '');
        $charset = 'utf8mb4';
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            self::$connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the PDO connection instance.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::init();
        }
        return self::$connection;
    }
}