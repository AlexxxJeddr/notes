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
            $config = require __DIR__ . '/../../config/db.php';
            
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
            
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
        }
        
        return self::$pdo;
    }

    public static function closeConnection(): void
    {
        self::$pdo = null;
    }
}
