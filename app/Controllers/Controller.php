<?php
/**
 * Базовый контроллер: доступ к шаблонам, конфигу и загрузке текстов.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

abstract class Controller
{
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
