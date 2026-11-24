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

<?php // Кейсы разбираем мы сами, и это видно. Строка про отзывы стоит
      // сразу после них: читатель только что прочитал нашу версию
      // и вправе узнать, где посмотреть чужую.
      //
      // Адрес профиля подставляется из настроек сайта: в тексте страницы
      // его нет, иначе при переезде профиля правок стало бы две. ?>
<?php if (!empty($page['reviews'])): ?>
    <?php $view->partial('sections/service-aside', [
        'aside' => $page['reviews'] + ['href' => $view->socialUrl($page['reviews']['link_id'])],
    ]); ?>
<?php endif; ?>

<?php $view->partial('sections/contact', ['form' => $page['form'], 'state' => $form]); ?>
