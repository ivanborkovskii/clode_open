<?php
/**
 * Роутер. Статические пути и параметры вида /stati/{slug}.
 * Новая страница = одна строка в config/routes.php.
 */

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, string>> метод => путь => 'Контроллер@метод' */
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    /**
     * Ищет обработчик для запроса.
     *
     * @return array{controller:string, action:string, params:array<int,string>}|null
     */
    public function match(string $method, string $uri): ?array
    {
        $path   = $this->normalize(parse_url($uri, PHP_URL_PATH) ?: '/');
        $routes = $this->routes[$method] ?? [];

        // Точное совпадение — самый частый случай.
        if (isset($routes[$path])) {
            return $this->handler($routes[$path], []);
        }

        foreach ($routes as $pattern => $handler) {
            if (!str_contains($pattern, '{')) {
                continue;
            }

            $regex = '#^' . preg_replace('#\{[a-z_]+\}#i', '([^/]+)', $pattern) . '$#';

            if (preg_match($regex, $path, $matches)) {
                return $this->handler($handler, array_slice($matches, 1));
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $params
     * @return array{controller:string, action:string, params:array<int,string>}
     */
    private function handler(string $handler, array $params): array
    {
        [$controller, $action] = explode('@', $handler, 2);

        return ['controller' => $controller, 'action' => $action, 'params' => $params];
    }

    /**
     * Канонический вид пути: без завершающего слэша.
     * Нужно, чтобы /uslugi и /uslugi/ не считались разными URL — иначе дубли в поиске.
     */
    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
