<?php
/**
 * Страница раздела «Статьи»: шапка, поиск с отбором, список, заявка.
 *
 * Одна и та же страница обслуживает общий список и страницы категорий —
 * отличаются только заголовок, набор карточек и адрес в фильтрах.
 *
 * @var App\Core\View $view
 * @var array $page
 * @var array $list
 * @var array $pagination
 * @var array $seo
 * @var array $form
 */
?>
<?php $view->partial('sections/page-hero', [
    'hero'   => $page['hero'],
    'crumbs' => $seo['breadcrumbs'],
]); ?>

<?php $view->partial('sections/article-filters', ['page' => $page]); ?>

<?php $view->partial('sections/article-list', [
    'list'       => $list,
    'pagination' => $pagination,
    'texts'      => $page['texts'],
    'filtered'   => $page['reset'] !== '',
]); ?>

<?php $view->partial('sections/contact', ['form' => $page['form'], 'state' => $form]); ?>
