<?php
/**
 * ЖУРНАЛ ОШИБОК
 * =============
 *
 * Зачем. На боевом сервере показ ошибок выключен намеренно: посетитель
 * не должен видеть пути к файлам и куски кода. Но выключен он был и для
 * нас — при фатальной ошибке страница просто отдавала «500», и почему
 * это случилось, узнать было неоткуда. Особенно плохо, когда ошибка
 * случается изредка: воспроизвести её не выходит, а следов не остаётся.
 *
 * Что делает. Записывает каждую ошибку в storage/logs/errors.log одной
 * строкой: время, адрес страницы, текст ошибки, файл и номер строки.
 * Посетителю по-прежнему показывается обычная страница с извинением.
 *
 * Ловится двумя способами, потому что одного мало:
 *   - register() ловит фатальные ошибки — те, после которых PHP
 *     останавливается и до обработчика исключений дело не доходит;
 *   - save() вызывается из точки входа для исключений, которые она
 *     поймала сама.
 *
 * Журнал не растёт бесконечно: при превышении размера старая половина
 * отбрасывается. Разбираться всё равно приходится со свежими записями,
 * а забить диск хостинга журналом — верный способ уронить сайт целиком.
 */

declare(strict_types=1);

namespace App\Core;

final class ErrorLog
{
    /** Предел размера журнала. Дальше старые записи вытесняются. */
    private const MAX_BYTES = 512 * 1024;

    /**
     * Ошибки, после которых PHP останавливается. Предупреждения и заметки
     * сюда не входят намеренно: их в журнале были бы тысячи, и за ними
     * потерялось бы то, ради чего он заведён.
     */
    private const FATAL = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR;

    /**
     * Ловить фатальные ошибки до конца работы.
     *
     * Такую ошибку нельзя перехватить обычным способом: PHP останавливается
     * сразу. Но перед остановкой он успевает выполнить то, что записано
     * здесь, — и последнюю ошибку ещё можно прочитать.
     */
    public static function register(string $dir): void
    {
        register_shutdown_function(static function () use ($dir): void {
            $error = error_get_last();

            if ($error === null || ($error['type'] & self::FATAL) === 0) {
                return;
            }

            self::save($dir, 'фатальная ошибка', $error['message'], $error['file'], $error['line']);
        });
    }

    /**
     * Записать одну ошибку.
     *
     * Все обращения к файлам с @: если записать не вышло — например,
     * на папке нет прав, — сайт из-за этого падать не должен. Журнал
     * нужен нам, а страница нужна посетителю.
     */
    public static function save(
        string $dir,
        string $kind,
        string $message,
        string $file,
        int $line,
    ): void {
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return;
        }

        $path = $dir . '/errors.log';

        self::trim($path);

        $entry = [
            'time'    => date('c'),
            'kind'    => $kind,
            'message' => $message,
            'where'   => $file . ':' . $line,
            'url'     => ($_SERVER['REQUEST_METHOD'] ?? '') . ' '
                . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? ''),
        ];

        @file_put_contents(
            $path,
            json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }

    /** Оставить в журнале только свежую половину, если он разросся. */
    private static function trim(string $path): void
    {
        if (!is_file($path) || (int) @filesize($path) <= self::MAX_BYTES) {
            return;
        }

        $content = @file_get_contents($path);

        if ($content === false) {
            return;
        }

        // Режем по границе строки, чтобы журнал не начинался с половины
        // записи и оставался разбираемым.
        $cut  = (int) (strlen($content) / 2);
        $from = strpos($content, PHP_EOL, $cut);

        @file_put_contents(
            $path,
            $from === false ? '' : substr($content, $from + strlen(PHP_EOL)),
            LOCK_EX,
        );
    }
}
