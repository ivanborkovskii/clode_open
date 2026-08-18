<?php
/**
 * Единая точка входа. Веб-сервер должен смотреть в каталог /public.
 */

declare(strict_types=1);

use App\Core\Autoloader;
use App\Core\Router;
use App\Core\View;

$root = dirname(__DIR__);

require $root . '/app/Core/Autoloader.php';
Autoloader::register($root . '/app');

$config = require $root . '/config/app.php';

error_reporting($config['debug'] ? E_ALL : 0);
ini_set('display_errors', $config['debug'] ? '1' : '0');

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

$router = new Router();
require $root . '/config/routes.php';

$view  = new View($root . '/app/Views', $config, $router->published());
$route = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'] ?? '/');

if ($route === null) {
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');

    echo $view->render('error', [
        'seo'     => ['title' => 'Страница не найдена', 'noindex' => true],
        'code'    => 404,
        'message' => 'Такой страницы нет. Возможно, она ещё не создана или адрес изменился.',
    ]);

    return;
}

$class = 'App\\Controllers\\' . $route['controller'];

$controller = new $class($view, $config);

try {
    $controller->{$route['action']}(...$route['params']);
} catch (PDOException) {
    // База нужна только разделу «Статьи». Если она недоступна, страница
    // должна честно ответить «временно недоступно», а не отдать пустой
    // документ с кодом 200: такую страницу поисковик проиндексирует
    // как пустую, а посетитель решит, что сайт сломан насовсем.
    http_response_code(503);
    header('Retry-After: 600');

    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch') {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['status' => 'error'], JSON_UNESCAPED_UNICODE);

        return;
    }

    header('Content-Type: text/html; charset=UTF-8');

    echo $view->render('error', [
        'seo'     => ['title' => 'Раздел временно недоступен', 'noindex' => true],
        'code'    => 503,
        'message' => 'Статьи сейчас недоступны из-за сбоя базы данных. '
            . 'Остальные разделы сайта работают.',
    ]);
}
