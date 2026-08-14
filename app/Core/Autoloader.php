<?php
/**
 * Минимальный PSR-4 автозагрузчик — чтобы не тянуть Composer ради одной функции.
 * Пространство App\ отображается на каталог /app.
 */

declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    public static function register(string $baseDir, string $prefix = 'App\\'): void
    {
        spl_autoload_register(static function (string $class) use ($baseDir, $prefix): void {
            if (!str_starts_with($class, $prefix)) {
                return;
            }

            $relative = substr($class, strlen($prefix));
            $path     = $baseDir . '/' . str_replace('\\', '/', $relative) . '.php';

            if (is_file($path)) {
                require_once $path;
            }
        });
    }
}
