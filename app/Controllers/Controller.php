<?php
/**
 * Базовый контроллер: доступ к шаблонам, конфигу и загрузке текстов.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;

abstract class Controller
{
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

    protected function html(string $body): void
    {
        header('Content-Type: text/html; charset=UTF-8');
        echo $body;
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
