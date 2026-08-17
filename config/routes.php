<?php
/**
 * Маршруты сайта.
 *
 * Внутренние страницы из архитектуры добавляются сюда по мере разработки —
 * закомментированные строки задают согласованную структуру URL.
 */

declare(strict_types=1);

use App\Controllers\ServiceController;
use App\Controllers\SolutionController;
use App\Core\Router;

/** @var Router $router */

$router->get('/', 'HomeController@index');

$router->get('/uslugi', 'ServiceController@index');
$router->get('/uslugi/{slug}', 'ServiceController@show', ServiceController::paths());

$router->get('/resheniya', 'SolutionController@index');
$router->get('/resheniya/{slug}', 'SolutionController@show', SolutionController::paths());

$router->post('/zayavka', 'LeadController@store');

$router->get('/privacy', 'LegalController@privacy');
$router->get('/soglasie', 'LegalController@consent');

$router->get('/sitemap.xml', 'SitemapController@index');
$router->get('/sitemap-pages.xml', 'SitemapController@pages');

/*
 * Следующие этапы (не разрабатываются без отдельной задачи):
 *
 * $router->get('/keysy', 'CaseController@index');
 * $router->get('/keysy/{slug}', 'CaseController@show');
 * $router->get('/stati', 'ArticleController@index');
 * $router->get('/stati/{slug}', 'ArticleController@show');
 * $router->get('/o-kompanii', 'PageController@about');
 * $router->get('/kontakty', 'PageController@contacts');
 */
