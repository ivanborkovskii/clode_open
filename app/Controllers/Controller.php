<?php
/**
 * Базовый контроллер: доступ к шаблонам, конфигу и загрузке текстов.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\View;
use PDO;

abstract class Controller
{
    /**
     * Соединение с базой. Открывается при первом обращении, поэтому
     * страницы без базы — а это весь сайт, кроме статей, — работают
     * и когда база недоступна.
     */
    protected function db(): PDO
    {
        return Database::connection($this->config['db']);
    }

    /**
     * Результат отправки формы заявки, сохранённый в сессии.
     *
     * Нужен любой странице с формой, поэтому лежит здесь, а не в контроллерах.
     * Заполняется только при отправке без JavaScript: со скриптом ответ
     * приходит в JSON и страница не перезагружается.
     *
     * @return array{status:string, errors:array<string,string>, values:array<string,string>}
     */
    protected function formFlash(): array
    {
        Session::start();

        $flash = $_SESSION['form_flash'] ?? [];
        unset($_SESSION['form_flash']);

        // Сессия могла не сохраниться — тогда результат берём из метки
        // в адресе, которую поставил контроллер заявок. Иначе человек
        // увидит пустую форму и решит, что заявка отправлена.
        $marker = $_GET['zayavka'] ?? '';
        $status = $flash['status'] ?? match ($marker) {
            'ok'    => 'success',
            'error' => 'error',
            default => '',
        };

        $errors = $flash['errors'] ?? [];

        if ($status === 'error' && $errors === []) {
            $errors['_form'] = 'Заявку отправить не удалось. Заполните поля ещё раз '
                . 'или позвоните: ' . $this->config['company']['phone'];
        }

        return [
            'status' => $status,
            'errors' => $errors,
            'values' => $flash['values'] ?? [],
        ];
    }

    public function __construct(
        protected readonly View $view,
        protected readonly array $config,
    ) {
    }

    /** Загружает файл с текстами из config/content. */
    protected function content(string $name): array
    {
        return require dirname(__DIR__, 2) . '/config/content/' . $name . '.php';
    }

    /** Полный URL страницы — для canonical и Open Graph. */
    protected function url(string $path = '/'): string
    {
        return $this->config['base_url'] . ($path === '/' ? '/' : '/' . trim($path, '/'));
    }

    /**
     * Страница «не найдено». Нужна разделам с адресами по параметру:
     * несуществующий адрес должен отдавать 404, а не пустую страницу с 200.
     */
    protected function notFound(): void
    {
        http_response_code(404);

        $this->html($this->view->render('error', [
            'seo'     => ['title' => 'Страница не найдена', 'noindex' => true],
            'code'    => 404,
            'message' => 'Такой страницы нет. Возможно, она ещё не создана или адрес изменился.',
        ]));
    }

    /**
     * Отдаёт готовую страницу.
     *
     * $cacheable = false — для админки: её страницы не должны оставаться
     * в памяти браузера после выхода.
     */
    protected function html(string $body, bool $cacheable = true): void
    {
        header('Content-Type: text/html; charset=UTF-8');

        if (!$cacheable) {
            header('Cache-Control: no-store');
            echo $body;

            return;
        }

        // private — страница личная: в ней токен формы, и через общий кэш
        //           её нельзя отдать другому человеку.
        // no-cache — хранить можно, но перед каждым показом спрашивать
        //           сервер. Это не то же самое, что no-store: тот запрещает
        //           хранить вовсе и заодно ломает возврат по кнопке «назад».
        header('Cache-Control: private, no-cache, must-revalidate');

        // Отпечаток страницы. Если у браузера уже лежит ровно эта версия,
        // отвечаем «не изменилось» — без тела. Экономится и трафик,
        // и время на отрисовку.
        //
        // Отпечаток считается от того, что реально уходит посетителю,
        // поэтому у страницы с чужим токеном формы он будет другим:
        // перепутать двух посетителей нельзя.
        $etag = '"' . md5($body) . '"';
        header('ETag: ' . $etag);

        // «Не изменилось» уместно только там, где иначе был бы обычный
        // успешный ответ. У страницы «не найдено» свой код, и подменять
        // его на 304 нельзя.
        if (http_response_code() === 200
            && self::etagMatches((string) ($_SERVER['HTTP_IF_NONE_MATCH'] ?? ''), $etag)
        ) {
            http_response_code(304);

            return;
        }

        echo $body;
    }

    /**
     * Тот ли это отпечаток, который мы выдали в прошлый раз.
     *
     * Сравнивать строки напрямую нельзя: до браузера отпечаток доходит
     * изменённым, и обратно приходит уже не тот, что ставил PHP.
     *
     *   * Apache при сжатии страницы дописывает в него -gzip:
     *     "abc" превращается в "abc-gzip";
     *   * nginx в такой же ситуации помечает его «слабым» — W/"abc";
     *   * браузер может прислать несколько отпечатков через запятую
     *     и звёздочку вместо любого из них.
     *
     * Из-за этого страницы отвечали 200 вместо 304, и аудит писал,
     * что браузерного кэширования нет.
     *
     * Слабое сравнение здесь и нужно: по правилам протокола ответ
     * «не изменилось» на нём и основан — важно, что страница та же
     * по смыслу, а не побайтово.
     */
    private static function etagMatches(string $header, string $etag): bool
    {
        $header = trim($header);

        if ($header === '') {
            return false;
        }

        foreach (explode(',', $header) as $candidate) {
            $candidate = trim($candidate);

            if ($candidate === '*' || self::bareEtag($candidate) === $etag) {
                return true;
            }
        }

        return false;
    }

    /** Отпечаток без пометки «слабый» и без следа сжатия. */
    private static function bareEtag(string $tag): string
    {
        if (str_starts_with($tag, 'W/')) {
            $tag = substr($tag, 2);
        }

        return preg_replace('/-(gzip|br|deflate)"$/', '"', $tag) ?? $tag;
    }

    protected function redirect(string $location, int $status = 302): void
    {
        http_response_code($status);
        header('Location: ' . $location);
    }

    /** @param array<string, mixed> $data */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
