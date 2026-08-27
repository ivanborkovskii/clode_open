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

    /** @var array<int, string> Готовые адреса у маршрутов с параметром */
    private array $declared = [];

    /**
     * @param array<int, string> $published Для маршрута с параметром — список
     *        уже разработанных адресов. По нему шаблоны решают, выводить
     *        ссылку или нет: из шаблона /uslugi/{slug} этого не видно.
     */
    public function get(string $path, string $handler, array $published = []): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;

        foreach ($published as $declaredPath) {
            $this->declared[] = $this->normalize($declaredPath);
        }
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    /**
     * Адреса всех готовых страниц сайта.
     *
     * Нужны шаблонам: пока раздел не разработан, ссылка на него не выводится.
     * Иначе меню и перечни ведут в «страница не найдена», а поисковик
     * находит на сайте битые ссылки.
     *
     * @return array<int, string>
     */
    public function published(): array
    {
        $static = array_filter(
            array_keys($this->routes['GET'] ?? []),
            static fn (string $path): bool => !str_contains($path, '{'),
        );

        return array_values(array_unique([...$static, ...$this->declared]));
    }

    /**
     * Ищет обработчик для запроса.
     *
     * @return array{controller:string, action:string, params:array<int,string>}|null
     */
    public function match(string $method, string $uri): ?array
    {
        $path = $this->normalize(parse_url($uri, PHP_URL_PATH) ?: '/');

        // HEAD — это тот же GET, только сервер не отдаёт тело ответа.
        // Так устроен протокол, и этим пользуются проверяльщики ссылок:
        // чтобы узнать код ответа, качать всю страницу незачем.
        //
        // Отдельных маршрутов для HEAD нет и быть не должно — иначе каждый
        // адрес пришлось бы объявлять дважды. Раньше такой запрос не находил
        // ни одного маршрута, и любая страница сайта отвечала на него
        // «не найдено». Тело ответа отбрасывает сам веб-сервер.
        $routes = $this->routes[$method === 'HEAD' ? 'GET' : $method] ?? [];

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
