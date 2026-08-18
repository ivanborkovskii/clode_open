<?php
/**
 * Маршруты сайта.
 *
 * Внутренние страницы из архитектуры добавляются сюда по мере разработки —
 * закомментированные строки задают согласованную структуру URL.
 */

declare(strict_types=1);

use App\Controllers\CaseController;
use App\Controllers\ServiceController;
use App\Controllers\SolutionController;
use App\Core\Router;

/** @var Router $router */

$router->get('/', 'HomeController@index');

$router->get('/uslugi', 'ServiceController@index');
$router->get('/uslugi/{slug}', 'ServiceController@show', ServiceController::paths());

$router->get('/resheniya', 'SolutionController@index');
$router->get('/resheniya/{slug}', 'SolutionController@show', SolutionController::paths());

$router->get('/keysy', 'CaseController@index');
$router->get('/keysy/{slug}', 'CaseController@show', CaseController::paths());

// Раздел «Статьи». Адрес /stati/kategoriya/{slug} длиннее, чем /stati/{slug},
// и параметр в шаблоне не захватывает слэш — поэтому страница категории
// не попадает в обработчик статьи.
$router->get('/stati', 'ArticleController@index');
$router->get('/stati/kategoriya/{slug}', 'ArticleController@category');
$router->get('/stati/{slug}', 'ArticleController@show');
$router->get('/api/poisk-statey', 'ArticleController@suggest');
$router->post('/stati/{slug}/kommentariy', 'ArticleController@comment');
$router->post('/stati/{slug}/ocenka', 'ArticleController@rate');

$router->get('/o-kompanii', 'PageController@about');
$router->get('/kontakty', 'PageController@contacts');

$router->post('/zayavka', 'LeadController@store');

$router->get('/privacy', 'LegalController@privacy');
$router->get('/soglasie', 'LegalController@consent');

$router->get('/sitemap.xml', 'SitemapController@index');
$router->get('/sitemap-pages.xml', 'SitemapController@pages');
$router->get('/sitemap-articles.xml', 'SitemapController@articles');

// Админка раздела «Статьи». Один адрес со своим внутренним разбором:
// заводить по маршруту на каждое действие незачем, а закрыть от индексации
// проще один путь.
$router->get('/admin', 'AdminController@handle');
$router->get('/admin/{action}', 'AdminController@handle');
$router->post('/admin/{action}', 'AdminController@handle');
