<?php
/**
 * Страница раздела «Услуги».
 *
 * Порядок: что за раздел → перечень услуг с составом работ →
 * принципы, общие для всех услуг → заявка.
 *
 * @var App\Core\View $view
 * @var array $page Тексты из config/content/services.php
 * @var array $seo
 * @var array $form Результат отправки формы
 */
?>
<?php $view->partial('sections/page-hero', [
    'hero'   => $page['hero'],
    'crumbs' => $seo['breadcrumbs'],
]); ?>

<?php $view->partial('sections/service-rows', ['items' => $page['items']]); ?>
<?php $view->partial('sections/approach',     ['approach' => $page['approach']]); ?>
<?php $view->partial('sections/contact',      ['form' => $page['form'], 'state' => $form]); ?>
