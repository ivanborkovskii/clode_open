<?php
/**
 * Запуск сессии. Один общий вызов на весь сайт.
 *
 * Сессия нужна для двух вещей: токена защиты формы от подделки запроса
 * и передачи результата отправки на страницу после редиректа.
 *
 * Файлы сессий кладутся в storage/sessions внутри проекта, а не в общий
 * каталог хостинга. На дешёвом хостинге общий каталог нередко недоступен
 * для записи или чистится чужими процессами — тогда токен не сохраняется,
 * проверка формы не проходит и заявки молча теряются. Свой каталог
 * снимает эту зависимость.
 */

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $dir = dirname(__DIR__, 2) . '/storage/sessions';

        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        // Если каталог создать не удалось, остаётся путь хостинга по умолчанию.
        if (is_dir($dir) && is_writable($dir)) {
            session_save_path($dir);
        }

        @session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
        ]);
    }
}
