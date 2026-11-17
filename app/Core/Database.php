<?php

declare(strict_types=1);

namespace Apoki\Core;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function configure(array $config): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            (string) $config['host'],
            (int) $config['port'],
            (string) $config['database'],
            (string) $config['charset']
        );

        self::$pdo = new PDO(
            $dsn,
            (string) $config['username'],
            (string) $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => true,
            ]
        );
    }

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            throw new RuntimeException('数据库连接尚未初始化。');
        }

        return self::$pdo;
    }

    public static function beginTransaction(): void
    {
        self::connection()->beginTransaction();
    }

    public static function commit(): void
    {
        self::connection()->commit();
    }

    public static function rollBack(): void
    {
        if (self::connection()->inTransaction()) {
            self::connection()->rollBack();
        }
    }
}
