<?php
/**
 * Страница «Контакты»: способы связи, реквизиты и форма.
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

<?php $view->partial('sections/contact-ways', ['ways' => $page['ways']]); ?>
<?php $view->partial('sections/requisites',   ['requisites' => $page['requisites']]); ?>
<?php $view->partial('sections/contact',      ['form' => $page['form'], 'state' => $form]); ?>
