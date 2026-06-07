<?php
// Database Connection Manager
declare(strict_types=1);

namespace App\Model;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $configPath = __DIR__ . '/../../config/db.php';
            
            if (!file_exists($configPath)) {
                throw new \RuntimeException("Database config file not found at: " . $configPath);
            }
            
            $config = require $configPath;
            
            // Validate config
            if (!isset($config['host'], $config['dbname'], $config['user'])) {
                throw new \RuntimeException("Invalid database config: missing required fields");
            }
            
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
            
            try {
                self::$pdo = new PDO(
                    $dsn,
                    $config['user'],
                    $config['pass'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new \RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }
        
        return self::$pdo;
    }

    public static function closeConnection(): void
    {
        self::$pdo = null;
    }
}
