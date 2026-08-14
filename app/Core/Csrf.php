<?php
/**
 * CSRF-токен для форм. Токен живёт в сессии и проверяется при POST.
 */

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        self::ensureSession();

        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY];
    }

    public static function check(?string $token): bool
    {
        self::ensureSession();

        $expected = $_SESSION[self::KEY] ?? '';

        return $expected !== '' && is_string($token) && hash_equals($expected, $token);
    }

    private static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
            ]);
        }
    }
}
