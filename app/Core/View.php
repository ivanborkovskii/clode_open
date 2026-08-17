<?php
/**
 * Рендеринг шаблонов.
 *
 * Один метод подключения — partial(). Страница собирается из секций:
 * $view->partial('sections/hero', [...]). Любую секцию можно переиспользовать
 * на внутренних страницах.
 */

declare(strict_types=1);

namespace App\Core;

final class View
{
    /** @param array<int, string> $published Адреса готовых страниц */
    public function __construct(
        private readonly string $viewPath,
        private readonly array $config,
        private readonly array $published = [],
    ) {
    }

    /**
     * Готова ли страница по этому адресу.
     *
     * Шаблоны выводят ссылку только на существующие страницы: сайт
     * выкладывается по разделам, и ссылка на неразработанный раздел
     * вела бы посетителя в «страница не найдена».
     */
    public function exists(string $path): bool
    {
        $path = '/' . trim($path, '/');

        return in_array($path === '/' ? '/' : rtrim($path, '/'), $this->published, true);
    }

    /**
     * Рендерит страницу внутри общего макета.
     *
     * @param array<string, mixed> $data
     */
    public function render(string $page, array $data = []): string
    {
        $data['config'] = $this->config;

        $content = $this->capture("pages/{$page}", $data);

        return $this->capture('layouts/base', $data + ['content' => $content]);
    }

    /**
     * Подключает шаблон: 'sections/hero', 'partials/header'.
     *
     * @param array<string, mixed> $data
     */
    public function partial(string $path, array $data = []): void
    {
        echo $this->capture($path, $data + ['config' => $this->config]);
    }

    /** @param array<string, mixed> $data */
    private function capture(string $template, array $data): string
    {
        $file = $this->viewPath . '/' . $template . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException("Шаблон не найден: {$template}");
        }

        $view = $this;
        extract($data, EXTR_SKIP);

        ob_start();
        require $file;

        return (string) ob_get_clean();
    }

    /** Экранирование вывода. Короткое имя — в шаблонах используется постоянно. */
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Путь к статике с версией — чтобы браузер забрал обновлённый файл. */
    public function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/') . '?v=' . ($this->config['assets_version'] ?? '1');
    }
}
