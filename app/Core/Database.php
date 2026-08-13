<?php
/**
 * Ленивое подключение к MySQL через PDO.
 * Соединение открывается только при первом обращении — главная странице БД не требует.
 */

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private static ?PDO $connection = null;

    /** @param array<string, mixed> $config Секция 'db' из config/app.php */
    public static function connection(array $config): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset'],
        );

        self::$connection = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Настоящие подготовленные запросы вместо эмуляции.
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return self::$connection;
    }
}
