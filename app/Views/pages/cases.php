<?php
/**
 * Страница раздела «Кейсы»: шапка, список проектов рядами, заявка.
 *
 * Список выводится той же секцией, что и на главной, — разница только
 * в данных: здесь все проекты и без кнопки «смотреть все».
 *
 * @var App\Core\View $view
 * @var array $page
 * @var array $seo
 * @var array $form
 */
?>
<?php $view->partial('sections/page-hero', [
    'hero'   => $page['hero'],
    'crumbs' => $seo['breadcrumbs'],
]); ?>

<?php $view->partial('sections/cases',   ['cases' => $page['list']]); ?>
<?php $view->partial('sections/contact', ['form' => $page['form'], 'state' => $form]); ?>
